<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * RESOURCEMUSING class
 *
 * Deal with resource's musings
 */
class RESOURCEMUSING
{
    private $gatekeep;
    private $db;
    private $vars;
    private $textqp;
    private $session;
    private $messages;
    private $errors;
    private $success;
    private $navigate;
    private $badInput;

    // Constructor
    public function __construct()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once("core/modules/resource/TEXTQP.php");
        $this->textqp = new TEXTQP();
        $this->session = FACTORY_SESSION::getInstance();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();

        $this->navigate = FACTORY_NAVIGATE::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "musings"));
        if (!array_key_exists('resourceId', $this->vars) || !$this->vars['resourceId'] ||
            !array_key_exists('method', $this->vars) || !$this->vars['method'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $function = $this->vars['method'];
        if (!method_exists($this, $function))
        {
            $this->navigate->resource($this->vars['resourceId'], $this->errors->text("inputError", "invalid"));
        }
    }
    /**
     * display the editing form
     */
    public function musingEdit()
    {
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = $tinymce->loadMetadataTextarea(['Text']);
        $pString .= \FORM\formHeader('resource_RESOURCEMUSING_CORE');
        $hidden = \FORM\hidden("resourceId", $this->vars['resourceId']);
        $hidden .= \FORM\hidden('method', 'edit');
        $metadata['hidden'] = $pString;
        $page_start = $page_end = $db_paragraph = $db_section = $db_chapter = $text = $private = FALSE;
        // are we editing or adding?
        if (array_key_exists('resourcemetadataId', $this->vars))
        {
            $hidden .= \FORM\hidden("resourcemetadataId", $this->vars['resourcemetadataId']);
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $recordset = $this->db->select(
                'resource_metadata',
                ['resourcemetadataId', 'resourcemetadataPageStart', 'resourcemetadataPageEnd', 'resourcemetadataText',
                    'resourcemetadataParagraph', 'resourcemetadataSection', 'resourcemetadataChapter', 'resourcemetadataPrivate', ]
            );
            $row = $this->db->fetchRow($recordset);
            $page_start = \HTML\dbToFormTidy($row['resourcemetadataPageStart']);
            $page_end = \HTML\dbToFormTidy($row['resourcemetadataPageEnd']);
            $db_paragraph = \HTML\dbToFormTidy($row['resourcemetadataParagraph']);
            $db_section = \HTML\dbToFormTidy($row['resourcemetadataSection']);
            $db_chapter = \HTML\dbToFormTidy($row['resourcemetadataChapter']);
            $text = \HTML\dbToFormTidy($row['resourcemetadataText']);
            $private = $row['resourcemetadataPrivate'];
        }
        else
        {
            $private = 'Y';
        }
        $metadata['keyword'] = $this->textqp->displayKeywordForm('musing', 'resourcemetadataId');
        $locations = \HTML\tableStart('left');
        $locations .= \HTML\trStart();
        $locations .= \HTML\td($hidden . \FORM\textInput(
            $this->messages->text("resources", "page"),
            'resourcemetadataPageStart',
            $page_start,
            6,
            5
        ) . "&nbsp;-&nbsp;" . \FORM\textInput(FALSE, 'resourcemetadataPageEnd', $page_end, 6, 5));
        $locations .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "paragraph"),
            'resourcemetadataParagraph',
            $db_paragraph,
            11,
            10
        ));
        $locations .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "section"),
            'resourcemetadataSection',
            $db_section,
            20
        ));
        $locations .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "chapter"),
            'resourcemetadataChapter',
            $db_chapter,
            20
        ));
        $locations .= \HTML\trEnd();
        $locations .= \HTML\tableEnd();
        $metadata['locations'] = $locations;
        // The second parameter ('musingText') to textareaInput is the textarea name
        $metadata['metadata'] = \FORM\textareaInput(FALSE, 'Text', $text, 80, 10);
        $metadata['metadataTitle'] = $this->messages->text("resources", 'musing');
        $this->db->formatConditions(['usergroupsusersUserId' => $this->session->getVar('setup_UserId')]);
        $this->db->leftJoin('user_groups', 'usergroupsId', 'usergroupsusersGroupId');
        $recordset3 = $this->db->select('user_groups_users', ['usergroupsusersGroupId', 'usergroupsTitle']);
        if ($this->db->numRows($recordset3))
        {
            $privateArray = ['Y' => $this->messages->text("resources", "private"),
                'N' => $this->messages->text("resources", "public"), ];
            while ($row = $this->db->fetchRow($recordset3))
            {
                $privateArray[$row['usergroupsusersGroupId']] =
                    $this->messages->text("resources", "availableToGroups", \HTML\dbToFormTidy($row['usergroupsTitle']));
            }
            $metadata['form']['private'] = \FORM\selectedBoxValue(
                $this->messages->text("resources", "musingPrivate"),
                "private",
                $privateArray,
                $private,
                3
            );
        }
        else
        {
            $privateArray = ['Y' => $this->messages->text("resources", "private"),
                'N' => $this->messages->text("resources", "public"), ];
            $metadata['form']['private'] = \FORM\selectedBoxValue(
                $this->messages->text("resources", "musingPrivate"),
                "private",
                $privateArray,
                $private,
                2
            );
        }
        $metadata['form']['submit'] = \FORM\formSubmit($this->messages->text("submit", "Save"));
        $metadata['formfoot'] = \FORM\formEnd();
        $this->session->delVar('resourceMusingLock');
        GLOBALS::setTplVar('metadata', $metadata);
        unset($metadata);
    }
    /**
     * write to the database
     *
     * if there is no 'musingId' input, we are adding a new musing.  Otherwise, editing one.
     */
    public function edit()
    {
        if ($this->session->getVar('resourceMusingLock'))
        {
            $this->badInput->close($this->errors->text("done", "musing"));
        }
        $this->checkInput();
        $userId = $this->session->getVar('setup_UserId');
        // insert
        if (!array_key_exists('resourcemetadataId', $this->vars))
        {
            if (!array_key_exists('Text', $this->vars) ||
                !trim($this->vars['Text']))
            {
                $this->badInput->close($this->errors->text("inputError", "missing"));
            }
            $message = $this->success->text("musingAdd");
            $fields[] = 'resourcemetadataResourceId';
            $values[] = trim($this->vars['resourceId']);
            if (array_key_exists('resourcemetadataPageStart', $this->vars) && $this->vars['resourcemetadataPageStart'])
            {
                $fields[] = 'resourcemetadataPageStart';
                $values[] = trim(mb_strtolower($this->vars['resourcemetadataPageStart']));
                if (array_key_exists('resourcemetadataPageEnd', $this->vars) && $this->vars['resourcemetadataPageEnd'])
                {
                    $fields[] = 'resourcemetadataPageEnd';
                    $values[] = trim(mb_strtolower($this->vars['resourcemetadataPageEnd']));
                }
            }
            if (array_key_exists('resourcemetadataParagraph', $this->vars) && $this->vars['resourcemetadataParagraph'])
            {
                $fields[] = 'resourcemetadataParagraph';
                $values[] = trim(mb_strtolower($this->vars['resourcemetadataParagraph']));
            }
            if (array_key_exists('resourcemetadataSection', $this->vars) && $this->vars['resourcemetadataSection'])
            {
                $fields[] = 'resourcemetadataSection';
                $values[] = trim(mb_strtolower($this->vars['resourcemetadataSection']));
            }
            if (array_key_exists('resourcemetadataChapter', $this->vars) && $this->vars['resourcemetadataChapter'])
            {
                $fields[] = 'resourcemetadataChapter';
                $values[] = trim(mb_strtolower($this->vars['resourcemetadataChapter']));
            }
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
            $values[] = 'm';
            if ($userId)
            {
                $fields[] = "resourcemetadataAddUserId";
                $values[] = $userId;
            }
            $this->db->insert('resource_metadata', $fields, $values);
            $lastAutoId = $this->db->lastAutoId();
            $this->textqp->summary(1, 'resourcesummaryMusings');
            $this->textqp->writeKeywords($lastAutoId, 'resourcekeywordMetadataId');
        }
        // else edit/delete?
        else
        {
            // if musingText is empty, delete the row
            if (!$this->vars['Text'])
            {
                $message = $this->success->text("musingDelete");
                $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_metadata');
                $this->db->formatConditions(['resourcekeywordMetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_keyword');
                $this->textqp->summary(-1, 'resourcesummaryMusings');
            }
            else
            {
                $message = $this->success->text("musingEdit");
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
                $updateArray['resourcemetadataTimestamp'] = $this->db->formatTimestamp();
                $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->update('resource_metadata', $updateArray);
                $updateArray = $nulls = [];
                // page number lowercased in case roman numerals input!
                if (array_key_exists('resourcemetadataPageStart', $this->vars) && $this->vars['resourcemetadataPageStart'])
                {
                    $updateArray['resourcemetadataPageStart'] = trim(mb_strtolower($this->vars['resourcemetadataPageStart']));
                    if (array_key_exists('resourcemetadataPageEnd', $this->vars) && $this->vars['resourcemetadataPageEnd'])
                    {
                        $updateArray['resourcemetadataPageEnd'] = trim(mb_strtolower($this->vars['resourcemetadataPageEnd']));
                    }
                    else
                    {
                        $nulls[] = 'resourcemetadataPageEnd';
                    }
                }
                else
                {
                    $nulls[] = 'resourcemetadataPageStart';
                    $nulls[] = 'resourcemetadataPageEnd';
                }
                if (array_key_exists('resourcemetadataParagraph', $this->vars) && $this->vars['resourcemetadataParagraph'])
                {
                    $updateArray['resourcemetadataParagraph'] = trim($this->vars['resourcemetadataParagraph']);
                }
                else
                {
                    $nulls[] = 'resourcemetadataParagraph';
                }
                if (array_key_exists('resourcemetadataSection', $this->vars) && $this->vars['resourcemetadataSection'])
                {
                    $updateArray['resourcemetadataSection'] = trim($this->vars['resourcemetadataSection']);
                }
                else
                {
                    $nulls[] = 'resourcemetadataSection';
                }
                if (array_key_exists('resourcemetadataChapter', $this->vars) && $this->vars['resourcemetadataChapter'])
                {
                    $updateArray['resourcemetadataChapter'] = trim($this->vars['resourcemetadataChapter']);
                }
                else
                {
                    $nulls[] = 'resourcemetadataChapter';
                }
                if (!empty($updateArray))
                {
                    $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                    $this->db->update('resource_metadata', $updateArray);
                }
                if (!empty($nulls))
                {
                    $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                    $this->db->updateNull('resource_metadata', $nulls);
                }
                $this->textqp->writeKeywords($this->vars['resourcemetadataId'], 'resourcekeywordMetadataId');
            }
        }
        // update resource_timestamp
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->notify($this->vars['resourceId']))
        {
            $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()));
        }
        // lock reload
        $this->session->setVar('resourceMusingLock', TRUE);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $message);
    }
    /**
     * Check we have appropriate input.  Page and comment are optional
     */
    private function checkInput()
    {
        if (!array_key_exists('resourceId', $this->vars) || !$this->vars['resourceId'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
    }
}
