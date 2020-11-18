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
    private $icons;
    private $return;
    private $navigate;
    private $badInput;
    private $formData = [];
    private $browserTabID = FALSE;

    // Constructor
    public function __construct()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "TEXTQP.php"]));
        $this->textqp = new TEXTQP();
        $this->textqp->type = 'musing';
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->navigate = FACTORY_NAVIGATE::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->browserTabID = GLOBALS::getBrowserTabID();
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
        $this->return = '&nbsp;&nbsp;' . \HTML\a(
            $this->icons->getClass("edit"),
            $this->icons->getHTML("Return"),
            'index.php?action=resource_RESOURCEVIEW_CORE&id=' . $this->vars['resourceId'] . '&browserTabID=' . $this->browserTabID
        );
    }
    /**
     * display the editing form
     *
     * @param mixed $message
     */
    public function musingEdit($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "musings") . $this->return);
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = $message;
        $pString .= $tinymce->loadMetadataTextarea(['Text']);
        $pString .= \FORM\formHeader('resource_RESOURCEMUSING_CORE');
        $hidden = \FORM\hidden("resourceId", $this->vars['resourceId']);
        $hidden .= \FORM\hidden('method', 'edit');
        $metadata['hidden'] = $pString;
        $page_start = $page_end = $db_paragraph = $db_section = $db_chapter = $text = $private = FALSE;
        $private = 'Y';
        if (!empty($this->formData))
        {
            $page_start = $this->formData['PageStart'];
            $page_end = $this->formData['PageEnd'];
            $db_paragraph = $this->formData['Paragraph'];
            $db_section = $this->formData['Section'];
            $db_chapter = $this->formData['Chapter'];
            $text = array_key_exists('Text', $this->formData) ? $this->formData['Text'] : FALSE;
            $private = $this->formData['private'];
        }
        // are we editing or adding?
        elseif (array_key_exists('resourcemetadataId', $this->vars))
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
        $metadata['keyword'] = $this->textqp->displayKeywordForm($this->formData);
        $locations = \HTML\tableStart('left');
        $locations .= \HTML\trStart();
        $locations .= \HTML\td($hidden . \FORM\textInput(
            $this->messages->text("resources", "page"),
            'PageStart',
            $page_start,
            6,
            5
        ) . "&nbsp;-&nbsp;" . \FORM\textInput(FALSE, 'PageEnd', $page_end, 6, 5));
        $locations .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "paragraph"),
            'Paragraph',
            $db_paragraph,
            11,
            10
        ));
        $locations .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "section"),
            'Section',
            $db_section,
            20
        ));
        $locations .= \HTML\td(\FORM\textInput(
            $this->messages->text("resources", "chapter"),
            'Chapter',
            $db_chapter,
            20
        ));
        $locations .= \HTML\trEnd();
        $locations .= \HTML\tableEnd();
        $metadata['locations'] = $locations;
        // The second parameter ('musingText') to textareaInput is the textarea name
        $metadata['metadata'] = \FORM\textareaInput(FALSE, 'Text', $text, 80, 10);
        $metadata['metadataTitle'] = $this->messages->text("resources", 'musing');
        $this->db->formatConditions(['usergroupsusersUserId' => $this->session->getVar("setup_UserId")]);
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
        $userId = $this->session->getVar("setup_UserId");
        $this->checkInput();
        // insert
        if (!array_key_exists('resourcemetadataId', $this->vars))
        {
            $message = $this->success->text("musingAdd");
            $fields[] = 'resourcemetadataResourceId';
            $values[] = \UTF8\mb_trim($this->vars['resourceId']);
            if (array_key_exists('PageStart', $this->vars) && $this->vars['PageStart'])
            {
                $fields[] = 'resourcemetadataPageStart';
                $values[] = \UTF8\mb_trim(mb_strtolower($this->vars['PageStart']));
                if (array_key_exists('PageEnd', $this->vars) && $this->vars['PageEnd'])
                {
                    $fields[] = 'resourcemetadataPageEnd';
                    $values[] = \UTF8\mb_trim(mb_strtolower($this->vars['PageEnd']));
                }
            }
            if (array_key_exists('Paragraph', $this->vars) && $this->vars['Paragraph'])
            {
                $fields[] = 'resourcemetadataParagraph';
                $values[] = trim(mb_strtolower($this->vars['Paragraph']));
            }
            if (array_key_exists('Section', $this->vars) && $this->vars['Section'])
            {
                $fields[] = 'resourcemetadataSection';
                $values[] = trim(mb_strtolower($this->vars['Section']));
            }
            if (array_key_exists('Chapter', $this->vars) && $this->vars['Chapter'])
            {
                $fields[] = 'resourcemetadataChapter';
                $values[] = trim(mb_strtolower($this->vars['Chapter']));
            }
            $fields[] = 'resourcemetadataText';
            $values[] = \UTF8\mb_trim($this->vars['Text']);
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
                $updateArray['resourcemetadataText'] = \UTF8\mb_trim($this->vars['Text']);
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
                if (array_key_exists('PageStart', $this->vars) && $this->vars['PageStart'])
                {
                    $updateArray['resourcemetadataPageStart'] = trim(mb_strtolower($this->vars['PageStart']));
                    if (array_key_exists('PageEnd', $this->vars) && $this->vars['PageEnd'])
                    {
                        $updateArray['resourcemetadataPageEnd'] = trim(mb_strtolower($this->vars['PageEnd']));
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
                if (array_key_exists('Paragraph', $this->vars) && $this->vars['Paragraph'])
                {
                    $updateArray['resourcemetadataParagraph'] = \UTF8\mb_trim($this->vars['Paragraph']);
                }
                else
                {
                    $nulls[] = 'resourcemetadataParagraph';
                }
                if (array_key_exists('Section', $this->vars) && $this->vars['Section'])
                {
                    $updateArray['resourcemetadataSection'] = \UTF8\mb_trim($this->vars['Section']);
                }
                else
                {
                    $nulls[] = 'resourcemetadataSection';
                }
                if (array_key_exists('Chapter', $this->vars) && $this->vars['Chapter'])
                {
                    $updateArray['resourcemetadataChapter'] = \UTF8\mb_trim($this->vars['Chapter']);
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
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "email", "EMAIL.php"]));
        $emailClass = new EMAIL();
        if (!$emailClass->notify($this->vars['resourceId']))
        {
            $message = $this->errors->text("inputError", "mail", GLOBALS::getError());
        }
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $message);
    }
    /**
     * Ask for confirmation for musing to be deleted
     */
    public function deleteInit()
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "musingDelete") . $this->return);
        $pString = \FORM\formHeader('resource_RESOURCEMUSING_CORE');
        $pString .= \FORM\hidden("method", 'delete');
        $pString .= \FORM\hidden("resourceId", $this->vars['resourceId']);
        $pString .= \FORM\hidden("resourcemetadataId", $this->vars['resourcemetadataId']);
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Confirm")));
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Delete the musing and all peripheral data
     */
    public function delete()
    {
        if (!array_key_exists('resourcemetadataId', $this->vars))
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
        $this->db->delete('resource_metadata');
        $this->db->formatConditions(['resourcekeywordMetadataId' => $this->vars['resourcemetadataId']]);
        $this->db->delete('resource_keyword');
        $this->textqp->summary(-1, 'resourcesummaryMusings');
        $this->db->formatConditions(['resourcetimestampId' => $this->vars['resourceId']]);
        $this->db->update('resource_timestamp', ['resourcetimestampTimestamp' => $this->db->formatTimestamp()]);
        // send back to view this resource with success message
        $this->navigate->resource($this->vars['resourceId'], $this->success->text("musingDelete"));
    }
    /**
     * Check we have appropriate input.
     */
    private function checkInput()
    {
        $error = '';
        if (!array_key_exists('resourceId', $this->vars) || !$this->vars['resourceId'])
        {
            $this->badInput->close($this->errors->text("inputError", "missing"));
        }
        $this->formData['PageStart'] = \UTF8\mb_trim($this->vars['PageStart']);
        $this->formData['PageEnd'] = \UTF8\mb_trim($this->vars['PageEnd']);
        $this->formData['Paragraph'] = \UTF8\mb_trim($this->vars['Paragraph']);
        $this->formData['Section'] = \UTF8\mb_trim($this->vars['Section']);
        $this->formData['Chapter'] = \UTF8\mb_trim($this->vars['Chapter']);
        $this->formData['keywords'] = \UTF8\mb_trim($this->vars['keywords']);
        $this->formData['private'] = $this->vars['private'];
        if (array_key_exists('resourcemetadataId', $this->vars))
        { // Editing
            $this->formData['resourcemetadataId'] = $this->vars['resourcemetadataId'];
        }
        elseif (array_key_exists('Text', $this->vars) && \UTF8\mb_trim($this->vars['Text']))
        { // Inserting
            $this->formData['Text'] = \UTF8\mb_trim($this->vars['Text']);
        }
        else
        {
            $error = $this->errors->text("inputError", "missing");
        }
        if ($error)
        {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'musingEdit');
        }
    }
}
