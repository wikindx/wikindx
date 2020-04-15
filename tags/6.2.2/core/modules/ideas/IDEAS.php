<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * IDEAS class
 *
 * Deal with ideas
 */
class IDEAS
{
    private $gatekeep;
    private $db;
    private $vars;
    private $textqp;
    private $metadata;
    private $session;
    private $messages;
    private $errors;
    private $success;
    private $badInput;
    private $ideas;

    // Constructor
    public function __construct()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/modules/resource/TEXTQP.php");
        $this->textqp = new TEXTQP();
        $this->metadata = FACTORY_METADATA::getInstance();
        $this->session = FACTORY_SESSION::getInstance();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();

        $this->badInput = FACTORY_BADINPUT::getInstance();
        include_once("core/modules/help/HELPMESSAGES.php");
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('ideas'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "ideas"));
        if ($this->session->getVar('setup_ReadOnly'))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"));
        }
        if (!array_key_exists('method', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $function = $this->vars['method'];
        if (!method_exists($this, $function))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"));
        }
    }
    /**
     * list ideas based on keyword
     */
    public function keywordIdeaList()
    {
        if ((!array_key_exists('resourcekeywordKeywordId', $this->vars) || !$this->vars['resourcekeywordKeywordId']))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $this->ideaList(TRUE);
    }
    /**
     * list available ideas
     */
    public function ideaList($keywordList = FALSE)
    {
        $this->session->delVar('search_Highlight');
        $icons = FACTORY_LOADICONS::getInstance();
        $cite = FACTORY_CITE::getInstance();
        $userObj = FACTORY_USER::getInstance();
        $pagingObject = FACTORY_PAGING::getInstance();
        $multiUser = $this->session->getVar('setup_MultiUser');
        $ideaList = $ideaListInfo = [];
        $index = 0;
        $backupPagingTotal = $this->session->getVar('setup_PagingTotal'); // Required for normal list operations 'last multi'
        if ($total = $this->session->getVar('setup_IdeaPagingTotal'))
        {
            $this->session->setVar('setup_PagingTotal', $total);
        }
        if ((!array_key_exists('PagingStart', $this->vars) || !$this->vars['PagingStart']))
        {
            $this->session->delVar('mywikindx_PagingStart'); // might be set from last multi resource list display
            $this->session->delVar('list_IdeaAllIds');
        }
        if (array_key_exists('order', $this->vars))
        {
            $order = $this->vars['order'];
        }
        else
        {
            $order = 'timestamp'; // currently the only one
        }
        $this->session->setVar("list_IdeaOrder", $order); // Just one means of ordering for now
        if (array_key_exists('ascDesc', $this->vars))
        {
            $this->db->ascDesc = $this->vars['ascDesc'];
        }
        elseif ($this->session->issetVar("list_IdeaAscDesc"))
        {
            $this->db->ascDesc = $this->session->getVar("list_IdeaAscDesc");
        }
        else
        {
            $this->db->ascDesc = $this->db->asc;
        }
        $this->session->setVar("list_IdeaAscDesc", $this->db->ascDesc);
        if ($order == 'timestamp')
        {
            $this->db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
        }
        // Check this user is allowed to read the idea.
        $this->metadata->setCondition('i');
        if ($keywordList)
        {
            $this->db->leftJoin('resource_keyword', 'resourcekeywordMetadataId', 'resourcemetadataId');
            $this->db->formatConditions(['resourcekeywordKeywordId' => $this->vars['resourcekeywordKeywordId']]);
            $this->db->formatConditions(['resourcekeywordMetadataId' => ' IS NOT NULL']);
            $this->db->formatConditions(['resourcemetadataType' => 'i']);
            $queryString = "index.php?action=ideas_IDEAS_CORE" .
                htmlentities("&method=" . 'keywordIdeaList') . "&resourcekeywordKeywordId=" . $this->vars['resourcekeywordKeywordId'];
        }
        else
        {
            $queryString = "index.php?action=ideas_IDEAS_CORE" . "&method=" . 'ideaList';
        }
        $ids = $threadIds = [];
        if (!$this->session->getVar('list_IdeaAllIds'))
        {
            $resultSet = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataMetadataId']);
            while ($row = $this->db->fetchRow($resultSet))
            {
                $ids[] = $row['resourcemetadataId'];
                if (!$row['resourcemetadataMetadataId'])
                {
                    $threadIds[] = $row['resourcemetadataId'];
                }
                elseif (array_search($row['resourcemetadataMetadataId'], $threadIds) === FALSE)
                {
                    $threadIds[] = $row['resourcemetadataMetadataId'];
                }
            }
            if (empty($ids))
            {
                $this->badInput->close($this->errors->text("inputError", "invalid"));
            }
            $this->session->setVar('setup_PagingTotal', count($ids));
            $this->session->setVar('setup_IdeaPagingTotal', $this->session->getVar('setup_PagingTotal'));
            $this->session->setVar('list_IdeaAllIds', base64_encode(serialize($ids)));
            $this->db->formatConditionsOneField($ids, 'resourcemetadataId');
            $threadIds = array_unique($threadIds);
            if (!empty($threadIds))
            {
                $this->session->setVar("list_IdeaAllThreadIds", base64_encode(serialize($threadIds)));
            }
        }
        else
        {
            $this->db->formatConditionsOneField(unserialize(base64_decode($this->session->getVar('list_IdeaAllIds'))), 'resourcemetadataId');
        }
        $pagingObject->queryString = $queryString;
        $pagingObject->getPaging();
        $this->session->setVar('setup_PagingTotal', $backupPagingTotal);
        // now get ideas
        $this->db->ascDesc = $this->session->getVar("list_IdeaAscDesc");
        if ($order == 'timestamp')
        {
            $this->db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
        }
        $this->db->limit($this->session->getVar('setup_Paging'), $pagingObject->start);
        $resultset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp', 'resourcemetadataTimestampEdited',
            'resourcemetadataMetadataId', 'resourcemetadataText', 'resourcemetadataAddUserId', 'resourcemetadataPrivate', ]);
        $threadIds = [];
        while ($row = $this->db->fetchRow($resultset))
        {
            if ($multiUser)
            {
                list($user) = $userObj->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
                if (!$row['resourcemetadataTimestampEdited'])
                {
                    $ideaList[$index]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']);
                }
                else
                {
                    $ideaList[$index]['user'] = $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']) .
                    ',&nbsp;' . $this->messages->text('hint', 'editedBy', $user . '&nbsp;' . $row['resourcemetadataTimestampEdited']);
                }
                GLOBALS::addTplVar('multiUser', TRUE);
            }
            if ($row['resourcemetadataAddUserId'] == $this->session->getVar('setup_UserId'))
            {
                $ideaList[$index]['links'] = $this->metadata->createLinks($row, TRUE, TRUE, TRUE);
            }
            else
            { // all others can add to the thread
                $ideaList[$index]['links'] = $this->metadata->createLinks($row, TRUE, FALSE, FALSE);
            }
            $ideaList[$index]['metadata'] = $cite->parseCitations($row['resourcemetadataText'], 'html');
            ++$index;
        }
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        if ($index > 1)
        {
            $pString = \FORM\formHeader('ideas_IDEAS_CORE');
            $pString .= \FORM\hidden('method', 'ideaList');
            if ($selected = $this->session->getVar("list_IdeaOrder"))
            {
                $pString .= \FORM\selectedBoxValue(
                    $this->messages->text("list", "order"),
                    "order",
                    ["timestamp" => $this->messages->text("list", "timestamp")],
                    $selected,
                    1
                );
            }
            else
            {
                $pString .= \FORM\selectFBoxValue(
                    $this->messages->text("list", "order"),
                    "order",
                    ["timestamp" => $this->messages->text("list", "timestamp")],
                    1
                );
            }
            if ($ascDesc = trim($this->session->getVar("list_IdeaAscDesc")))
            {
                if ($ascDesc == 'ASC')
                {
                    $pString .= \HTML\p(\FORM\radioButton(FALSE, "ascDesc", 'ASC', TRUE) . $this->messages->text("list", "ascending") .
                        BR . \FORM\radioButton(FALSE, "ascDesc", 'DESC') . $this->messages->text("list", "descending"));
                }
                else
                {
                    $pString .= \HTML\p(\FORM\radioButton(FALSE, "ascDesc", 'ASC') . $this->messages->text("list", "ascending") .
                        BR . \FORM\radioButton(FALSE, "ascDesc", 'DESC', TRUE) . $this->messages->text("list", "descending"));
                }
            }
            else
            {
                $pString .= \HTML\p(\FORM\radioButton(FALSE, "ascDesc", 'ASC', TRUE) . $this->messages->text("list", "ascending") .
                    BR . \FORM\radioButton(FALSE, "ascDesc", 'DESC') . $this->messages->text("list", "descending"));
            }
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed"), 'Submit'));
            $pString .= \FORM\formEnd();
            $ideaListInfo['reorder'] = $pString;
            GLOBALS::setTplVar('ideaListInfo', $ideaListInfo);
        }
        $this->session->saveState('list');
        GLOBALS::addTplVar('ideaList', $ideaList);
    }
    /**
     * view an idea thread
     */
    public function threadView($ideaId = FALSE, $message = FALSE)
    {
        if (!$ideaId && (!array_key_exists('resourcemetadataId', $this->vars) || !$this->vars['resourcemetadataId']))
        {
            $this->session->setVar('sql_LastThread', FALSE);
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if (!$ideaId)
        {
            $ideaId = $this->vars['resourcemetadataId'];
        }
        if ($message)
        {
            GLOBALS::addTplVar('content', $message);
        }
        // Check this user is allowed to read the idea.
        $this->metadata->setCondition('i');
        $this->db->formatConditions(['resourcemetadataId' => $ideaId]);
        $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
        if (!$this->db->numRows($resultset))
        {
            $this->session->setVar('sql_LastThread', FALSE);
            $this->badInput->close($this->errors->text("inputError", "invalid"));
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "ideaThread"));
        $this->session->setVar('sql_LastThread', $ideaId);
        $this->metadata->displayThread($ideaId);
    }
    /**
     * display the adding/editing form for a sub idea
     */
    public function subIdeaForm()
    {
        if (!array_key_exists('resourcemetadataId', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        if (!$this->session->getVar('setup_UserId'))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"));
        }
        $this->ideas = [];
        $text = FALSE;
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = $tinymce->loadMetadataTextarea(['Text']);
        $pString .= \FORM\formHeader('ideas_IDEAS_CORE');
        $hidden = \FORM\hidden('method', 'subIdeaAdd');
        $hidden .= \FORM\hidden("resourcemetadataId", $this->vars['resourcemetadataId']);
        $metadataId = $this->vars['resourcemetadataId'];
        // are we editing or adding?
        if (array_key_exists('resourcemetadataMetadataId', $this->vars))
        { // editing
            // Check this user is allowed to edit the idea.
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $this->db->formatConditions(['resourcemetadataAddUserId' => $this->session->getVar('setup_UserId')]);
            $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
            if (!$this->db->numRows($resultset))
            {
                $this->session->setVar('sql_LastThread', FALSE);
                $this->badInput->close($this->errors->text("inputError", "invalid"));
            }
            $hidden .= \FORM\hidden("resourcemetadataMetadataId", $this->vars['resourcemetadataMetadataId']);
            $metadataId = $this->vars['resourcemetadataMetadataId'];
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $recordset = $this->db->select('resource_metadata', 'resourcemetadataText');
            $row = $this->db->fetchRow($recordset);
            $text = \HTML\dbToTinyMCE($row['resourcemetadataText']);
            $idea['keyword'] = $this->textqp->displayKeywordForm('idea', 'resourcemetadataMetadataId');
            $icons = FACTORY_LOADICONS::getInstance();
            $add = $icons->getHTML("add");
            $idea['add'] = \HTML\a($icons->getClass("add"), $add, "index.php?action=ideas_IDEAS_CORE" . htmlentities("&method=" . 'subIdeaForm') .
                htmlentities("&resourcemetadataId=" . $this->vars['resourcemetadataMetadataId']));
        }
        else
        {
            $idea['keyword'] = $this->textqp->displayKeywordForm('idea', 'resourcemetadataId');
        }
        $idea['hidden'] = $pString . $hidden;
        // The second parameter ('Text') to textareaInput is the textarea name
        $idea['idea'] = \FORM\textareaInput(FALSE, 'Text', $text, 80, 10);
        $idea['ideaTitle'] = $this->messages->text("metadata", 'subIdea');
        $idea['form']['submit'] = \FORM\formSubmit($this->messages->text("submit", "Save"));
        $idea['formfoot'] = \FORM\formEnd();
        $this->otherIdeas($metadataId, TRUE);
        $this->otherIdeas($metadataId, FALSE, 1);
        $idea['otherIdeas'] = $this->ideas;
        $this->session->delVar('ideaLock');
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('idea', $idea);
    }
    /**
     * display the adding/editing form for the main idea
     */
    public function ideaEdit()
    {
        $text = $metadataId = $owner = FALSE;
        $thisUserId = $this->session->getVar('setup_UserId');
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = $tinymce->loadMetadataTextarea(['Text']);
        $pString .= \FORM\formHeader('ideas_IDEAS_CORE');
        $hidden = \FORM\hidden('method', 'edit');
        $idea['hidden'] = $pString;
        // are we editing or adding?
        if (array_key_exists('resourcemetadataId', $this->vars))
        { // editing
            // Check this user is allowed to edit the idea.
            $this->db->formatConditions(['resourcemetadataAddUserId' => $this->session->getVar('setup_UserId')]);
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
            if (!$this->db->numRows($resultset))
            {
                $this->session->setVar('sql_LastThread', FALSE);
                $this->badInput->close($this->errors->text("inputError", "invalid"));
            }
            $hidden .= \FORM\hidden("resourcemetadataId", $this->vars['resourcemetadataId']);
            $metadataId = $this->vars['resourcemetadataId'];
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $recordset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataText',
                'resourcemetadataPrivate', 'resourcemetadataAddUserId', ]);
            $row = $this->db->fetchRow($recordset);
            if ($row['resourcemetadataAddUserId'] == $thisUserId)
            {
                $owner = TRUE;
            }
            $text = \HTML\dbToTinyMCE($row['resourcemetadataText']);
            $private = $row['resourcemetadataPrivate'];
            $icons = FACTORY_LOADICONS::getInstance();
            $add = $icons->getHTML("add");
            $idea['add'] = \HTML\a($icons->getClass("add"), $add, "index.php?action=ideas_IDEAS_CORE" . htmlentities("&method=" . 'subIdeaForm') .
                htmlentities("&resourcemetadataId=" . $this->vars['resourcemetadataId']));
        }
        else
        {
            $private = 'Y';
        }
        $idea['keyword'] = $hidden . $this->textqp->displayKeywordForm('idea', 'resourcemetadataId');
        // The second parameter ('Text') to textareaInput is the textarea name
        $idea['idea'] = \FORM\textareaInput(FALSE, 'Text', $text, 80, 10);
        $idea['ideaTitle'] = $this->messages->text("metadata", 'idea');
        $this->db->formatConditions(['usergroupsusersUserId' => $thisUserId]);
        $this->db->leftJoin('user_groups', 'usergroupsId', 'usergroupsusersGroupId');
        $recordset3 = $this->db->select('user_groups_users', ['usergroupsusersGroupId', 'usergroupsTitle']);
        $privateArray = ['Y' => $this->messages->text("resources", "private"),
            'N' => $this->messages->text("resources", "public"), ];
        if ($this->db->numRows($recordset3))
        {
            while ($row = $this->db->fetchRow($recordset3))
            {
                $privateArray[$row['usergroupsusersGroupId']] =
                    $this->messages->text("resources", "availableToGroups", \HTML\dbToFormTidy($row['usergroupsTitle']));
            }
            $idea['form']['private'] = \FORM\selectedBoxValue(
                $this->messages->text("resources", "ideaPrivate"),
                "private",
                $privateArray,
                $private,
                3
            );
        }
        else
        {
            $idea['form']['private'] = \FORM\selectedBoxValue(
                $this->messages->text("resources", "ideaPrivate"),
                "private",
                $privateArray,
                $private,
                2
            );
        }
        $idea['form']['submit'] = \FORM\formSubmit($this->messages->text("submit", "Save"));
        $idea['formfoot'] = \FORM\formEnd();
        if ($metadataId)
        {
            $this->otherIdeas($metadataId);
            $idea['otherIdeas'] = $this->ideas;
        }
        $this->session->delVar('ideaLock');
        GLOBALS::addTplVar('ideaTemplate', TRUE);
        GLOBALS::addTplVar('idea', $idea);
    }
    /**
     * Write to the database a main idea
     *
     * if there is no 'resourcemetadataId' input, we are adding a new idea.  Otherwise, editing one.
     */
    public function edit()
    {
        if ($this->session->getVar('ideaLock'))
        {
            $this->error($this->errors->text("done", "idea"));
        }
        $userId = $this->session->getVar('setup_UserId');
        // insert
        if (!array_key_exists('resourcemetadataId', $this->vars))
        {
            if (!array_key_exists('Text', $this->vars) || !trim(\HTML\stripHtml($this->vars['Text'])))
            {
                $this->badInput->close($this->errors->text("inputError", "missing"));
            }
            $message = $this->success->text("ideaAdd");
            $fields[] = 'resourcemetadataText';
            $values[] = trim($this->vars['Text']);
            $fields[] = 'resourcemetadataPrivate';
            if (array_key_exists('private', $this->vars) && ($this->vars['private'] == 'N'))
            {
                $values[] = 'N';
            }
            elseif (array_key_exists('private', $this->vars) && (is_numeric($this->vars['private'])))
            {
                $values[] = $this->vars['private'];
            }
            else
            {
                $values[] = 'Y';
            }
            $fields[] = 'resourcemetadataTimestamp';
            $values[] = $this->db->formatTimestamp();
            $fields[] = 'resourcemetadataType';
            $values[] = 'i';
            if ($userId)
            {
                $fields[] = "resourcemetadataAddUserId";
                $values[] = $userId;
            }
            $this->db->insert('resource_metadata', $fields, $values);
            $ideaId = $lastAutoId = $this->db->lastAutoId();
            $this->textqp->writeKeywords($lastAutoId, 'resourcekeywordMetadataId');
        }
        // else edit/delete?
        else
        {
            // Check this user is allowed to edit the idea.
            $this->db->formatConditions(['resourcemetadataAddUserId' => $this->session->getVar('setup_UserId')]);
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
            if (!$this->db->numRows($resultset))
            {
                $this->session->setVar('sql_LastThread', FALSE);
                $this->badInput->close($this->errors->text("inputError", "invalid"));
            }
            $mainIdea = FALSE;
            // is this the main idea?
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $row = $this->db->selectFirstRow('resource_metadata', 'resourcemetadataMetadataId');
            if (!$row['resourcemetadataMetadataId'])
            { // main idea
                $ideaId = $this->vars['resourcemetadataId'];
                $mainIdea = TRUE;
            }
            else
            {
                $ideaId = $row['resourcemetadataMetadataId'];
            }
            // if Text is empty, delete the row
            if (!$this->vars['Text'])
            {
                $message = $this->success->text("ideaDelete");
                $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_metadata');
                $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]); // delete subideas in thread
                $this->db->delete('resource_metadata');
                $this->db->formatConditions(['resourcekeywordMetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_keyword');
                $keyword = FACTORY_KEYWORD::getInstance();
                $keyword->removeHanging();
                include_once("core/display/FRONT.php");
                $front = new FRONT($message); // __construct() runs on autopilot
                FACTORY_CLOSE::getInstance();
            }
            else
            {
                $message = $this->success->text("ideaEdit");
                $updateArray = [];
                $updateArray['resourcemetadataText'] = trim($this->vars['Text']);
                if (array_key_exists('private', $this->vars) && ($this->vars['private'] == 'N'))
                {
                    $updateArray['resourcemetadataPrivate'] = 'N';
                }
                elseif (array_key_exists('private', $this->vars) && (is_numeric($this->vars['private'])))
                {
                    $updateArray['resourcemetadataPrivate'] = $this->vars['private'];
                }
                else
                {
                    $updateArray['resourcemetadataPrivate'] = 'Y';
                }
                $updateArray['resourcemetadataTimestampEdited'] = $this->db->formatTimestamp();
                $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->update('resource_metadata', $updateArray);
                $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->update('resource_metadata', ['resourcemetadataPrivate' => $updateArray['resourcemetadataPrivate']]);
                $updateArray = [];
                if (!empty($updateArray))
                {
                    $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                    $this->db->update('resource_metadata', $updateArray);
                }
                $this->textqp->writeKeywords($this->vars['resourcemetadataId'], 'resourcekeywordMetadataId');
            }
        }
        // lock reload
        $this->session->setVar('ideaLock', TRUE);
        // send back to idea thread with success message
        $this->threadView($ideaId, $message);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Write to the database a subidea
     *
     * if there is no 'resourcemetadataMetadataId' input, we are adding a new idea.  Otherwise, editing one.
     */
    public function subIdeaAdd()
    {
        if ($this->session->getVar('ideaLock'))
        {
            $this->error($this->errors->text("done", "idea"));
        }
        $userId = $this->session->getVar('setup_UserId');
        $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
        $row = $this->db->selectFirstRow('resource_metadata', 'resourcemetadataPrivate');
        // insert
        if (!array_key_exists('resourcemetadataMetadataId', $this->vars))
        {
            if (!array_key_exists('Text', $this->vars) || !trim(\HTML\stripHtml($this->vars['Text'])))
            {
                $this->badInput->close($this->errors->text("inputError", "missing"));
            }
            $message = $this->success->text("ideaAdd");
            $fields[] = 'resourcemetadataText';
            $values[] = trim($this->vars['Text']);
            $fields[] = 'resourcemetadataMetadataId';
            $values[] = $this->vars['resourcemetadataId'];
            $fields[] = 'resourcemetadataPrivate';
            $values[] = $row['resourcemetadataPrivate'];
            $fields[] = 'resourcemetadataTimestamp';
            $values[] = $this->db->formatTimestamp();
            $fields[] = 'resourcemetadataType';
            $values[] = 'i';
            if ($userId)
            {
                $fields[] = "resourcemetadataAddUserId";
                $values[] = $userId;
            }
            $this->db->insert('resource_metadata', $fields, $values);
            $this->textqp->writeKeywords($this->vars['resourcemetadataId'], 'resourcekeywordMetadataId');
            $returnId = $this->vars['resourcemetadataId'];
        }
        // else edit/delete?
        else
        {
            // Check this user is allowed to edit the idea.
            $this->db->formatConditions(['resourcemetadataAddUserId' => $this->session->getVar('setup_UserId')]);
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
            if (!$this->db->numRows($resultset))
            {
                $this->session->setVar('sql_LastThread', FALSE);
                $this->badInput->close($this->errors->text("inputError", "invalid"));
            }
            // if Text is empty, delete the row
            if (!$this->vars['Text'])
            {
                $message = $this->success->text("ideaaDelete");
                $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_metadata');
                $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]); // delete subideas in thread
                $this->db->delete('resource_metadata');
                $this->db->formatConditions(['resourcekeywordMetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_keyword');
                $keyword = FACTORY_KEYWORD::getInstance();
                $keyword->removeHanging();
                include_once("core/display/FRONT.php");
                $front = new FRONT($message); // __construct() runs on autopilot
                FACTORY_CLOSE::getInstance();
            }
            else
            {
                $message = $this->success->text("ideaEdit");
                $updateArray = [];
                $updateArray['resourcemetadataText'] = trim($this->vars['Text']);
                $updateArray['resourcemetadataTimestampEdited'] = $this->db->formatTimestamp();
                $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->update('resource_metadata', $updateArray);
                $this->textqp->writeKeywords($this->vars['resourcemetadataMetadataId'], 'resourcekeywordMetadataId');
                $returnId = $this->vars['resourcemetadataMetadataId'];
            }
        }
        // lock reload
        $this->session->setVar('ideaLock', TRUE);
        // send back to idea thread with success message
        $this->threadView($returnId, $message);
        FACTORY_CLOSE::getInstance();
    }
    /**
     * Delete a subIdea
     */
    public function delete()
    {
        if (!array_key_exists('resourcemetadataId', $this->vars) || !$this->vars['resourcemetadataId'])
        {
            $this->error($this->errors->text("inputError", "missing"));
        }
        // Check this user is allowed to delete the idea.
        $this->db->formatConditions(['resourcemetadataAddUserId' => $this->session->getVar('setup_UserId')]);
        $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
        $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
        if (!$this->db->numRows($resultset))
        {
            $this->error($this->errors->text("inputError", "invalid"));
        }
        $message = $this->success->text("ideaDelete");
        $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
        $this->db->delete('resource_metadata');
        if (array_key_exists('resourcemetadataMetadataId', $this->vars))
        {
            $this->threadView($this->vars['resourcemetadataMetadataId'], $message);
            FACTORY_CLOSE::getInstance();
        }
        include_once("core/display/FRONT.php");
        $front = new FRONT($message); // __construct() runs on autopilot
        FACTORY_CLOSE::getInstance();
    }
    /**
     * When deleting a thread, ask for confirmation
     */
    public function deleteConfirm()
    {
        if (!array_key_exists('resourcemetadataId', $this->vars) || !$this->vars['resourcemetadataId'])
        {
            $this->error($this->errors->text("inputError", "missing"));
        }
        // Check this user is allowed to delete the idea.
        $this->db->formatConditions(['resourcemetadataAddUserId' => $this->session->getVar('setup_UserId')]);
        $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
        $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
        if (!$this->db->numRows($resultset))
        {
            $this->error($this->errors->text("inputError", "invalid"));
        }
        $pString = \FORM\formHeader('ideas_IDEAS_CORE');
        $pString .= \FORM\hidden('method', 'deleteThread');
        $pString .= \FORM\hidden('resourcemetadataId', $this->vars['resourcemetadataId']);
        $pString .= $this->errors->text("warning", "ideaDelete") . BR;
        $pString .= \FORM\formSubmit($this->messages->text("submit", "Confirm"));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete a thread
     */
    public function deleteThread()
    {
        if (!array_key_exists('resourcemetadataId', $this->vars) || !$this->vars['resourcemetadataId'])
        {
            $this->error($this->errors->text("inputError", "missing"));
        }
        // Check this user is allowed to delete the idea.
        $this->db->formatConditions(['resourcemetadataAddUserId' => $this->session->getVar('setup_UserId')]);
        $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
        $resultset = $this->db->select('resource_metadata', 'resourcemetadataId');
        if (!$this->db->numRows($resultset))
        {
            $this->error($this->errors->text("inputError", "invalid"));
        }
        $message = $this->success->text("ideaDelete");
        $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
        $this->db->delete('resource_metadata');
        $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]); // delete subideas in thread
        $this->db->delete('resource_metadata');
        $this->db->formatConditions(['resourcekeywordMetadataId' => $this->vars['resourcemetadataId']]);
        $this->db->delete('resource_keyword');
        $keyword = FACTORY_KEYWORD::getInstance();
        $keyword->removeHanging();
        include_once("core/display/FRONT.php");
        $front = new FRONT($message); // __construct() runs on autopilot
        FACTORY_CLOSE::getInstance();
    }
    /**
     * error function
     */
    private function error($error)
    {
        if (array_key_exists('resourcemetadataMetadataId', $this->vars))
        {
            $this->threadView($this->vars['resourcemetadataMetadataId'], $error);
        }
        elseif (array_key_exists('resourcemetadataId', $this->vars))
        {
            $this->threadView($this->vars['resourcemetadataId'], $error);
        }
        else
        {
            $this->session->setVar('sql_LastThread', FALSE);
            $this->badInput->close($this->errors->text("inputError", "invalid"));
        }
        FACTORY_CLOSE::getInstance();
    }
    /**
     * display other ideas/subideas in the thread when adding a new subidea or editing
     */
    private function otherIdeas($metadataId, $main = FALSE, $index = 0)
    {
        $cite = FACTORY_CITE::getInstance();
        $userObj = FACTORY_USER::getInstance();
        $multiUser = $this->session->getVar('setup_MultiUser');
        if ($main)
        {
            $icons = FACTORY_LOADICONS::getInstance();
            $view = $icons->getHTML("viewmeta");
            $this->ideas[$index]['links'][] = \HTML\a($icons->getClass("viewmeta"), $view, "index.php?action=ideas_IDEAS_CORE" .
                htmlentities("&method=threadView&resourcemetadataId=" . $metadataId));
            $this->db->formatConditions(['resourcemetadataId' => $metadataId]);
        }
        else
        {
            $this->db->formatConditions(['resourcemetadataMetadataId' => $metadataId]);
        }
        $this->db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
        $recordset = $this->db->select('resource_metadata', ['resourcemetadataId', 'resourcemetadataTimestamp', 'resourcemetadataTimestampEdited',
            'resourcemetadataText', 'resourcemetadataMetadataId', 'resourcemetadataAddUserId', ]);
        while ($row = $this->db->fetchRow($recordset))
        {
            if ((!$main && $row['resourcemetadataId'] == $metadataId) ||
                (array_key_exists('resourcemetadataMetadataId', $this->vars) && ($this->vars['resourcemetadataId'] == $row['resourcemetadataId'])))
            {
                continue;
            }
            if ($multiUser)
            {
                list($user) = $userObj->displayUserAddEdit($row['resourcemetadataAddUserId'], FALSE, 'idea');
                if (!$row['resourcemetadataTimestampEdited'])
                {
                    $this->ideas[$index]['user'] = $ideaList[0]['user'] =
                        $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']);
                }
                else
                {
                    $this->ideas[$index]['user'] = $ideaList[0]['user'] =
                    $this->messages->text('hint', 'addedBy', $user . '&nbsp;' . $row['resourcemetadataTimestamp']) .
                    ',&nbsp;' . $this->messages->text('hint', 'editedBy', $user . '&nbsp;' . $row['resourcemetadataTimestampEdited']);
                }
            }
            $this->ideas[$index]['text'] = $cite->parseCitations($row['resourcemetadataText'], 'html');
            ++$index;
        }
    }
}