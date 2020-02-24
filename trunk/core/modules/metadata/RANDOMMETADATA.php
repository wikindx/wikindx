<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */


/**
 * Display Random Metadata.
 */
class RANDOMMETADATA
{
    private $db;
    private $icons;
    private $badInput;
    private $errors;
    private $messages;
    private $session;
    private $common;
    private $coins;
    private $bibStyle;
    private $user;
    private $stats;
    private $resourceLink = FALSE;
    private $cite;
    private $metadata;
    private $userId;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        $this->coins = FACTORY_EXPORTCOINS::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance();
        $this->stats = FACTORY_STATISTICS::getInstance();
        $this->cite = FACTORY_CITE::getInstance();
        $this->metadata = FACTORY_METADATA::getInstance();
        $this->userId = $this->session->getVar("setup_UserId");
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "randomMetadata"));
    }
    /**
     * Select a random quote ID for viewing
     */
    public function randomQuote()
    {
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("misc", "noQuotes"));
        }
        $row = $this->db->fetchRow($resultset);
        $resourceId = $row['resourcemetadataResourceId'];
        $mArray = $this->cite->parseCitations(\HTML\nlToHtml($row['resourcemetadataText']), 'html');
        $resultset = $this->common->getResource($resourceId);
        $this->display($resultset, $mArray, 'randomQuote');
    }
    /**
     * Select a random paraphrase ID for viewing
     */
    public function randomParaphrase()
    {
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("misc", "noParaphrases"));
        }
        $row = $this->db->fetchRow($resultset);
        $resourceId = $row['resourcemetadataResourceId'];
        $mArray = $this->cite->parseCitations(\HTML\nlToHtml($row['resourcemetadataText']), 'html');
        $resultset = $this->common->getResource($resourceId);
        $this->display($resultset, $mArray, 'randomParaphrase');
    }
    /**
     * Select a random musing ID for viewing
     */
    public function randomMusing()
    {
        if ($userId = $this->session->getVar('setup_UserId'))
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
        }
        elseif ($this->session->getVar('setup_ReadOnly'))
        {
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        }
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataResourceId', 'resourcemetadataText']);
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("misc", "noMusings"));
        }
        $row = $this->db->fetchRow($resultset);
        $resourceId = $row['resourcemetadataResourceId'];
        $mArray = $this->cite->parseCitations(\HTML\nlToHtml($row['resourcemetadataText']), 'html');
        $resultset = $this->common->getResource($resourceId);
        $this->display($resultset, $mArray, 'randomMusing');
    }
    /**
     * Select a random idea ID for viewing
     */
    public function randomIdea()
    {
        if (!$this->metadata->setCondition('i') && $this->session->getVar('setup_ReadOnly'))
        {
            $this->db->formatConditions(['resourcemetadataPrivate' => 'N']);
        }
        $this->db->formatConditions(['resourcemetadataType' => 'i']);
        $this->db->limit(1, 0);
        $this->db->orderByRandom();
        $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
        if (!$this->db->numRows($resultset))
        {
            $this->badInput->close($this->messages->text("misc", "noIdeas"));
        }
        $row = $this->db->fetchRow($resultset);
        $this->metadata->displayIdea($row['resourcemetadataId']);
    }
    /**
     * Produce a list of resources
     *
     * @param mixed $resultset
     * @param mixed $mArray
     * @param mixed $method
     *
     * @return bool Always TRUE
     */
    public function display($resultset, $mArray, $method)
    {
        $row = $this->db->fetchRow($resultset);
        if (GLOBALS::getUserVar('setup_ListLink'))
        {
            $this->resourceLink = "index.php?action=resource_RESOURCEVIEW_CORE" . htmlentities("&id=" . $row['resourceId']);
            $this->bibStyle->linkUrl = FALSE;
        }
        if (($row['resourcemiscQuarantine'] == 'Y') && (WIKINDX_QUARANTINE))
        {
            $resourceList[0]['quarantine'] = $this->icons->getHTML("quarantine");
        }
        $multiUser = $this->session->getVar('setup_MultiUser');
        if ($multiUser)
        {
            $resourceList[0]['user'] = $this->user->displayUserAddEdit($row);
            $popularityIndex = $this->messages->text("misc", "popIndex", $this->stats->getPopularityIndex($row['resourceId']));
            $maturityIndex = $row['resourcemiscMaturityIndex'] ?
                "&nbsp;" . $this->messages->text("misc", "matIndex") . "&nbsp;" .
                $row['resourcemiscMaturityIndex'] . "/10" . BR
                :
                FALSE;
            $resourceList[0]['popIndex'] = $popularityIndex;
            $resourceList[0]['maturity'] = $maturityIndex;
            GLOBALS::addTplVar('multiUser', TRUE);
        }
        $resourceList[0]['timestamp'] = $row['resourcetimestampTimestamp'];
        $resourceList[0]['links'] = $this->createLinks($row);
        if ($this->resourceLink)
        {
            $resourceList[0]['resource'] = \HTML\a('rLink', $this->bibStyle->process($row), $this->resourceLink) .
                $this->coins->export($row, $this->bibStyle->coinsCreators);
        }
        else
        {
            $resourceList[0]['resource'] = $this->bibStyle->process($row) . $this->coins->export($row, $this->bibStyle->coinsCreators);
        }
        $resourceList[0]['metadata'][] = $mArray;
        $resourceList[0]['navigation']['forward'] = $this->nextRandomLink($row['resourceId'], $method);
        GLOBALS::setTplVar('resourceList', $resourceList);
        unset($resourceList);

        return TRUE;
    }
    /**
     * Create links for viewing, editing deleting etc. resources
     *
     * @param mixed $row
     *
     * @return string[]
     */
    private function createLinks($row)
    {
        $write = $this->session->getVar('setup_Write');
        $links = [];
        $edit = FALSE;
        $view = $this->icons->getHTML("viewmeta");
        $links['view'] = \HTML\a($this->icons->getClass("viewmeta"), $view, "index.php?action=resource_RESOURCEVIEW_CORE" .
            htmlentities("&id=" . $row['resourceId']));
        if ($write && (!WIKINDX_ORIGINATOR_EDIT_ONLY || ($row['resourcemiscAddUserIdResource'] == $this->userId)))
        {
            $links['edit'] = \HTML\a(
                $this->icons->getClass("edit"),
                $this->icons->getHTML("edit"),
                "index.php?action=resource_RESOURCEFORM_CORE&type=edit" . htmlentities("&id=" . $row['resourceId'])
            );
            $edit = TRUE;
        }
        if ($this->session->getVar('setup_Superadmin'))
        {
            if (!$edit)
            {
                $links['edit'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=resource_RESOURCEFORM_CORE&type=edit" . htmlentities("&id=" . $row['resourceId'])
                );
            }
            $links['delete'] =
                "index.php?action=admin_DELETERESOURCE_CORE" . htmlentities('&function=deleteResourceConfirm');
            $links['delete'] .= htmlentities('&navigate=front&resource_id=' . $row['resourceId']);
            $links['delete'] = \HTML\a($this->icons->getClass("delete"), $this->icons->getHTML("delete"), $links['delete']);
        }
        // display CMS link if required
        // link is actually a JavaScript call
        if (GLOBALS::getUserVar('DisplayCmsLink'))
        {
            $links['cms'] = \HTML\a(
                'cmsLink',
                "CMS:&nbsp;" . $row['resourceId'],
                "javascript:coreOpenPopup('index.php?action=cms_CMS_CORE&amp;method=display" . "&amp;id=" . $row['resourceId'] . "', 90)"
            );
        }
        // display bibtex link if required
        // link is actually a JavaScript call
        if (GLOBALS::getUserVar('DisplayBibtexLink'))
        {
            $links['bibtex'] = \HTML\a(
                $this->icons->getClass("bibtex"),
                $this->icons->getHTML("bibtex"),
                "javascript:coreOpenPopup('index.php?action=resource_VIEWBIBTEX_CORE&amp;method=display" .
                "&amp;id=" . $row['resourceId'] . "', 90)"
            );
        }

        return $links;
    }
    /**
     * Show random resource hyperlink
     *
     * @param int $thisId
     * @param string $method
     *
     * @return string
     */
    private function nextRandomLink($thisId, $method)
    {
        /*
        $this->nextDelete = FALSE;
        if (($raw = $this->session->getVar("list_AllIds")) === FALSE)
            return FALSE;
        $allIds = unserialize(base64_decode($raw));
        $thisKey = array_search($thisId, $allIds);
        if ($this->session->getVar('setup_Superadmin'))
        {
            if(array_key_exists($thisKey + 1, $allIds))
                $this->nextDelete = $allIds[$thisKey + 1];
            else if(array_key_exists($thisKey - 1, $allIds))
                $this->nextDelete = $allIds[$thisKey - 1];
        }
        */
        return \HTML\a(
            $this->icons->getClass("next"),
            $this->icons->getHTML("next"),
            htmlentities("index.php?action=metadata_RANDOMMETADATA_CORE&method=$method")
        );
    }
}
