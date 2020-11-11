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
 * METADATA
 *
 * Common methods for metadata
 *
 * @package wikindx\core\metadata
 */
class METADATA
{
    /** object */
    private $db;
    /** object */
    private $icons;
    /** object */
    private $badInput;
    /** object */
    private $errors;
    /** object */
    private $messages;
    /** object */
    private $session;
    /** object */
    private $user;
    /** object */
    private $cite;
    /** object */
    private $common;
    /** int */
    private $userId;

    /**
     * METADATA
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->cite = FACTORY_CITE::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        $this->userId = $this->session->getVar("setup_UserId");
    }
    /**
     * Display a list of ideas (e.g. from random metadata).
     *
     * @param int $metadataId
     *
     * @return true
     */
    public function displayThread($metadataId)
    {
        $multiUser = WIKINDX_MULTIUSER;
        $ideaList = [];
        $index = 0;
        $tempSep = $this->db->conditionSeparator;
        $this->db->conditionSeparator = $this->db->or;
        $this->db->formatConditions(['resourcemetadataId' => $metadataId, 'resourcemetadataMetadataId' => $metadataId]);
        $this->db->conditionSeparator = $tempSep;
        $this->db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp', 'resourcemetadataTimestampEdited',
            'resourcemetadataMetadataId', 'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
        while ($row = $this->db->fetchRow($resultset))
        {
            if ($multiUser)
            {
                list($user) = $this->user->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
                if (!$row['resourcemetadataTimestampEdited'])
                {
                    $ideaList[$index]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']);
                }
                else
                {
                    $ideaList[$index]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']) . ',&nbsp;' .
                    $this->messages->text('hint', 'editedBy', $user . '&nbsp;' . $row['resourcemetadataTimestampEdited']);
                }
                GLOBALS::addTplVar('multiUser', TRUE);
            }
            $ideaList[$index]['timestamp'] = $row['resourcemetadataTimestamp'];
            if ($row['resourcemetadataAddUserId'] == $this->session->getVar("setup_UserId"))
            {
                $ideaList[$index]['links'] = $this->createLinks($row, FALSE, TRUE, TRUE);
            }
            $ideaList[$index]['metadata'] =
                $this->common->doHighlight($this->cite->parseCitations($row['resourcemetadataText'], 'html'));
            if (!$index)
            { // keywords only for main idea
                $this->db->formatConditions(['resourcekeywordMetadataId' => $row['resourcemetadataId']]);
                $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
                $recordset2 = $this->db->select('resource_keyword', ['keywordId', 'keywordKeyword']);
                while ($row2 = $this->db->fetchRow($recordset2))
                {
                    $ideaList[$index]['keywordTitle'] = $this->messages->text("resources", "keywords");
                    $ideaList[$index]['keywords'][] = \HTML\a(
                        "link",
                        \HTML\nlToHtml($row2['keywordKeyword']),
                        "index.php?action=ideas_IDEAS_CORE" .
                        htmlentities("&method=" . 'keywordIdeaList') . htmlentities("&resourcekeywordKeywordId=" . $row2['keywordId'])
                    );
                }
                $ideaList[$index]['links'][] =
                    \HTML\a($this->icons->getClass("add"), $this->icons->getHTML("add"), "index.php?action=ideas_IDEAS_CORE" . htmlentities("&method=" . 'subIdeaForm') .
                    htmlentities("&resourcemetadataId=" . $row['resourcemetadataId']));
            }
            ++$index;
        }
        $return = $this->previousNextLinks($metadataId);
        if (!empty($return))
        {
            GLOBALS::addTplVar('navigation', $return);
        }
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('ideaList', $ideaList);

        return TRUE;
    }
    /**
     * Display a single idea (e.g. from random metadata).
     *
     * @param int $metadataId
     *
     * @return true
     */
    public function displayIdea($metadataId)
    {
        $multiUser = WIKINDX_MULTIUSER;
        $ideaList = [];
        $this->db->formatConditions(['resourcemetadataId' => $metadataId]);
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp', 'resourcemetadataTimestampEdited',
            'resourcemetadataMetadataId', 'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
        $row = $this->db->fetchRow($resultset);
        if ($multiUser)
        {
            list($user) = $this->user->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
            if (!$row['resourcemetadataTimestampEdited'])
            {
                $ideaList[0]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']);
            }
            else
            {
                $ideaList[0]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']) .
                ',&nbsp;' . $this->messages->text('hint', 'editedBy', $user . '&nbsp;' . $row['resourcemetadataTimestampEdited']);
            }
            GLOBALS::addTplVar('multiUser', TRUE);
        }
        if ($row['resourcemetadataAddUserId'] == $this->session->getVar("setup_UserId"))
        {
            $ideaList[0]['links'] = $this->createLinks($row, TRUE, TRUE, TRUE);
        }
        $ideaList[0]['metadata'] = $this->cite->parseCitations($row['resourcemetadataText'], 'html');
        $nextLink['forward'] = \HTML\a(
            $this->icons->getClass("next"),
            $this->icons->getHTML("next"),
            htmlentities("index.php?action=metadata_RANDOMMETADATA_CORE&method=randomIdea")
        );
        GLOBALS::addTplVar('navigation', $nextLink);
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('ideaList', $ideaList);

        return TRUE;
    }
    /**
     * Create links for viewing, editing, deleting ideas
     *
     * @param array $row
     * @param bool $view Default = FALSE
     * @param bool $edit Default = FALSE
     * @param bool $delete Default = FALSE
     *
     * @return array
     */
    public function createLinks($row, $view = FALSE, $edit = FALSE, $delete = FALSE)
    {
        $links = [];
        if ($view)
        {
            $view = $this->icons->getHTML("viewmeta");
            if (!$row['resourcemetadataMetadataId'])
            { // i.e. this is the inital post in the idea thread
                $id = $row['resourcemetadataId'];
            }
            else
            {
                $id = $row['resourcemetadataMetadataId'];
            }
            $links[] = \HTML\a($this->icons->getClass("viewmeta"), $view, "index.php?action=ideas_IDEAS_CORE" .
                htmlentities("&method=threadView&resourcemetadataId=" . $id));
        }
        if ($edit)
        {
            $edit = $this->icons->getHTML("edit");
            $id = $row['resourcemetadataId'];
            // is this the main idea?
            if (!$row['resourcemetadataMetadataId'])
            { // main idea
                $links[] = \HTML\a($this->icons->getClass("edit"), $edit, "index.php?action=ideas_IDEAS_CORE" .
                    htmlentities("&method=ideaEdit&resourcemetadataId=" . $id));
            }
            else
            {
                $links[] = \HTML\a($this->icons->getClass("edit"), $edit, "index.php?action=ideas_IDEAS_CORE" .
                    htmlentities("&method=subIdeaForm&resourcemetadataId=" . $id) .
                    htmlentities("&resourcemetadataMetadataId=" . $row['resourcemetadataMetadataId']));
            }
        }
        if ($delete)
        {
            $delete = $this->icons->getHTML("delete");
            $id = $row['resourcemetadataId'];
            // is this the main idea?
            if (!$row['resourcemetadataMetadataId'])
            { // main idea
                $links[] = \HTML\a($this->icons->getClass("delete"), $delete, "index.php?action=ideas_IDEAS_CORE" .
                    htmlentities("&method=deleteConfirm&resourcemetadataId=" . $id));
            }
            else
            {
                $links[] = \HTML\a($this->icons->getClass("delete"), $delete, "index.php?action=ideas_IDEAS_CORE" .
                    htmlentities("&method=delete&resourcemetadataId=" . $id) .
                    htmlentities("&resourcemetadataMetadataId=" . $row['resourcemetadataMetadataId']));
            }
        }

        return $links;
    }
    /** set user/group ID conditions
     *
     * @param string $type One of 'm', 'i', 'qc', 'pc' for musings, ideas, quote comments, paraphrase comments. If FALSE (default), all except 'i' are returned
     * @param bool $returnString Execute condition (FALSE) or return the condition string (TRUE). Default is FALSE
     * @param bool $readOnly Create condition (TRUE) for read-only if no userId found in sessions. Default is FALSE (no condition created).
     *
     * @return bool
     */
    public function setCondition($type = FALSE, $returnString = FALSE, $readOnly = FALSE)
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
            if ($returnString)
            {
                $returnString = $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3, '=', TRUE);
            }
            else
            {
                $this->db->formatConditions($case1 . $this->db->or . $case2 . $this->db->or . $case3);
            }
            if ($type)
            {
                if ($returnString)
                {
                    return $returnString . $this->db->and . $this->db->formatConditions(['resourcemetadataType' => $type], '=', $returnString);
                }
                else
                {
                    $this->db->formatConditions(['resourcemetadataType' => $type]);
                }
            }
            else
            {
                if ($returnString)
                {
                    return $returnString . $this->db->and . $this->db->formatConditions(['resourcemetadataType' => 'i'], '!=', $returnString);
                }
                else
                {
                    $this->db->formatConditions(['resourcemetadataType' => 'i'], '!=');
                }
            }

            return TRUE;
        }
        // else, read-only user so give access to quotes, paraphrases and public musings
        elseif ($readOnly)
        {
            if ($type && ($type != 'i'))
            {
                if ($returnString)
                {
                    if (($type == 'pc') || ($type == 'qc') || ($type == 'm'))
                    {
                        return $this->db->formatConditions(['resourcemetadataType' => $type]) . $this->db->and .
                            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
                    }

                    return $returnString . $this->db->and . $this->db->formatConditions(['resourcemetadataType' => $type]);
                }
                else
                {
                    if (($type == 'pc') || ($type == 'qc') || ($type == 'm'))
                    {
                        $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
                    }
                    $this->db->formatConditions(['resourcemetadataType' => $type]);
                }
            }
            else
            {
                if ($returnString)
                {
                    return $this->db->formatConditionsOnefield(['q', 'p', 'm'], 'resourcemetadataType');
                }
                else
                {
                    $this->db->formatConditionsOneField(['i', 'qc', 'pc', 'm'], 'resourcemetadataType', '!=');
                }
            }

            return TRUE;
        }
        

        return FALSE;
    }
    /**
     * Show previous and next idea thread hyperlinks.
     *
     * @param int $thisId
     *
     * @return array
     */
    private function previousNextLinks($thisId)
    {
        $array = [];
        if (($raw = $this->session->getVar("list_IdeaAllThreadIds")) === FALSE)
        {
            return $array;
        }
        $allIds = unserialize(base64_decode($raw));
        if (!isset($allIds))
        {
            return $array;
        }
        $thisKey = array_search($thisId, $allIds);
        if ($thisKey === FALSE)
        {
            return $array;
        }
        if ($thisKey)
        {
            $array['back'] = \HTML\a(
                $this->icons->getClass("previous"),
                $this->icons->getHTML("previous"),
                "index.php?action=ideas_IDEAS_CORE" . htmlentities("&method=threadView&resourcemetadataId=" . $allIds[$thisKey - 1])
            );
        }
        else
        {
            $array['back'] = FALSE;
        }
        if ($thisKey < (count($allIds) - 1))
        {
            $array['forward'] = \HTML\a(
                $this->icons->getClass("next"),
                $this->icons->getHTML("next"),
                "index.php?action=ideas_IDEAS_CORE" . htmlentities("&method=threadView&resourcemetadataId=" . $allIds[$thisKey + 1])
            );
        }
        else
        {
            $array['forward'] = FALSE;
        }
        if ($this->session->getVar("setup_Superadmin"))
        {
            if (array_key_exists($thisKey + 1, $allIds))
            {
                $this->nextDelete = $allIds[$thisKey + 1];
            }
            elseif (array_key_exists($thisKey - 1, $allIds))
            {
                $this->nextDelete = $allIds[$thisKey - 1];
            }
        }

        return $array;
    }
}
