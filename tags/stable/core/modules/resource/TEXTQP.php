<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * TEXTQP class
 *
 * Common functions for quotes and paraphrases
 */
class TEXTQP
{
    private $db;
    private $vars;
    private $session;
    private $messages;
    private $errors;
    private $user;
    private $keyword;
    private $cite;
    private $badInput;

    // Constructor
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();

        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->user = FACTORY_USER::getInstance();

        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->cite = FACTORY_CITE::getInstance();

        $this->badInput = FACTORY_BADINPUT::getInstance();
        \AJAX\loadJavascript($this->config->WIKINDX_BASE_URL . '/core/modules/resource/resourceCategoryEdit.js');
    }
    /**
     * display the editing form
     *
     * Only the original poster can edit the quote/paraphrase and only the original posters can edit their comment
     *
     * @param string $type is either 'quote' or 'paraphrase'
     *
     * @return array
     */
    public function editDisplay($type)
    {
        $pageStart = 'PageStart';
        $pageEnd = 'PageEnd';
        $paragraph = 'Paragraph';
        $section = 'Section';
        $chapter = 'Chapter';
        $typeText = 'Text';
        $typeComment = 'Comment';
        $userId = $this->session->getVar('setup_UserId');
        $hidden = \FORM\hidden("resourceId", $this->vars['resourceId']);
        $page_start = $page_end = $db_paragraph = $db_section = $db_chapter = $text = $comment = FALSE;
        $private = 'Y';
        // are we editing or adding?
        if (array_key_exists('resourcemetadataId', $this->vars))
        {
            $hidden .= \FORM\hidden('resourcemetadataId', $this->vars['resourcemetadataId']);
            $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
            $row = $this->db->selectFirstRow('resource_metadata', ['resourcemetadataText', 'resourcemetadataPageStart',
                'resourcemetadataPageEnd', 'resourcemetadataParagraph', 'resourcemetadataSection', 'resourcemetadataChapter', 'resourcemetadataAddUserId', ]);
            $page_start = \HTML\dbToFormTidy($row['resourcemetadataPageStart']);
            $db_paragraph = \HTML\dbToFormTidy($row['resourcemetadataParagraph']);
            $db_section = \HTML\dbToFormTidy($row['resourcemetadataSection']);
            $db_chapter = \HTML\dbToFormTidy($row['resourcemetadataChapter']);
            if ($row['resourcemetadataPageEnd'])
            {
                $page_end = \HTML\dbToFormTidy($row['resourcemetadataPageEnd']);
            }
            if ($userId == $row['resourcemetadataAddUserId'])
            {
                $text = \HTML\dbToFormTidy($row['resourcemetadataText']);
            }
            else
            {
                $text = \HTML\dbToHtmlTidy($row['resourcemetadataText']);
            }
            // Get user's comment
            $this->db->formatConditions(['resourcemetadataAddUserId' => $userId]);
            $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]);
            $recordset = $this->db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataPrivate']);
            if ($this->db->numRows($recordset))
            {
                $rowComment = $this->db->fetchRow($recordset);
                $comment = \HTML\dbToFormTidy($rowComment['resourcemetadataText']);
                $private = $rowComment['resourcemetadataPrivate'];
            }
        }
        if (isset($row) && $userId != $row['resourcemetadataAddUserId'])
        {
            $hidden .= \FORM\hidden("commentOnly", TRUE);
        }
        if (!isset($row) || ($text && ($userId == $row['resourcemetadataAddUserId'])))
        {
            $metadata['keyword'] = $this->displayKeywordForm($type, 'resourcemetadataId');
        }
        if (!$text || ($text && ($userId == $row['resourcemetadataAddUserId'])))
        {
            $locations = \HTML\tableStart('left');
            $locations .= \HTML\trStart();
            $locations .= \HTML\td($hidden . \FORM\textInput(
                $this->messages->text("resources", "page"),
                $pageStart,
                $page_start,
                6,
                5
            ) . '-&nbsp;' . \FORM\textInput(FALSE, $pageEnd, $page_end, 6, 5));
            $locations .= \HTML\td(\FORM\textInput(
                $this->messages->text("resources", "paragraph"),
                $paragraph,
                $db_paragraph,
                11,
                10
            ));
            $locations .= \HTML\td(\FORM\textInput(
                $this->messages->text("resources", "section"),
                $section,
                $db_section,
                20
            ));
            $locations .= \HTML\td(\FORM\textInput(
                $this->messages->text("resources", "chapter"),
                $chapter,
                $db_chapter,
                20
            ));
            $locations .= \HTML\trEnd();
            $locations .= \HTML\tableEnd();
            $metadata['locations'] = $locations;
        }
        elseif ($page_start && $page_end)
        {
            $page = \HTML\strong('pp.' . $page_start . '-&nbsp;' . $page_end) . "&nbsp;";
        }
        elseif ($page_start)
        {
            $page = \HTML\strong('p.' . $page_start) . "&nbsp;";
        }
        else
        {
            $page = FALSE;
        }
        $hint = ($type == 'quote') ? \HTML\span($this->messages->text("hint", $type), 'hint') : FALSE;
        // The second parameter ($typeText) to textareaInput is the textarea name
        if (!$text || ($text && ($userId == $row['resourcemetadataAddUserId'])))
        {
            $metadata['metadata'] = \FORM\textareaInput(FALSE, $typeText, $text, 80, 10) . $hint;
            $metadata['metadataTitle'] = $this->messages->text("resources", $type);
        }
        else
        {
            $metadata['original'] = $hidden . $page . $this->cite->parseCitations($text, 'html');
        }
        // The second parameter ($typeComment) to textareaInput is the textarea name
        $metadata['comment'] = \FORM\textareaInput(FALSE, $typeComment, $comment, 80, 10);
        $metadata['commentTitle'] = $this->messages->text("resources", "comment");
        $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
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
                $this->messages->text("resources", "commentPrivate"),
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
                $this->messages->text("resources", "commentPrivate"),
                "private",
                $privateArray,
                $private,
                2
            );
        }
        $metadata['form']['submit'] = \FORM\formSubmit($this->messages->text("submit", "Save"));
        // display other comments
        if ($text)
        {
            $this->db->formatConditions(['resourcemetadataAddUserId' => $userId], TRUE);
            $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]);
            $this->db->ascDesc = $this->db->desc;
            $this->db->orderBy('resourcemetadataTimestamp', TRUE, FALSE);
            $recordset = $this->db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataPrivate',
                'resourcemetadataAddUserId', 'resourcemetadataTimestamp', ]);
            $index = 0;
            while ($row = $this->db->fetchRow($recordset))
            {
                if (($row['resourcemetadataPrivate'] == 'Y') && ($userId != $row['resourcemetadataAddUserId']))
                {
                    continue;
                }
                elseif (is_numeric($row['resourcemetadataPrivate']))
                {
                    $this->db->formatConditions(['usergroupsusersUserId' => $userId]);
                    $this->db->formatConditions(['usergroupsusersGroupId' => $row['resourcemetadataPrivate']]);
                    $recordset3 = $this->db->select('user_groups_users', 'usergroupsusersId');
                    if (!$this->db->numRows($recordset3))
                    {
                        continue;
                    }
                }
                // Comment can be viewed
                $username = $this->user->displayUserAddEdit($row['resourcemetadataAddUserId'], TRUE, 'comment');
                $metadata['otherComments'][$index]['comment'] = $this->cite->parseCitations(\HTML\dbToHtmlTidy($row['resourcemetadataText']), 'html');
                $metadata['otherComments'][$index]['userAdd'] = $username[0];
                $metadata['otherComments'][$index]['timestamp'] = $row['resourcemetadataTimestamp'];
                ++$index;
            }
        }

        return $metadata;
    }
    /**
     * display keyword form
     *
     * @param string $type
     * @param int $textId
     *
     * @return string
     */
    public function displayKeywordForm($type, $textId)
    {
        $keywords = $this->keyword->grabAll();
        $pString = \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        if (!empty($keywords))
        {
            // If preferences reduce long keywords, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($keywords as $key => $value)
            {
                $key = $key . '_' . base64_encode($value);
                $keywordList[$key] = $value;
            }
            if (array_key_exists($textId, $this->vars))
            { // editing
                $field = 'resourcekeywordMetadataId';
                $this->db->formatConditions([$field => $this->vars[$textId]]);
                $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
                $this->db->orderBy('keywordKeyword');
                $resultset = $this->db->select('resource_keyword', 'keywordKeyword');
                while ($row = $this->db->fetchRow($resultset))
                {
                    $keywordArray[] = $row['keywordKeyword'];
                }
            }
            // If this is a new metadata entry (not editing), populate textarea with resource's keywords
            elseif (!array_key_exists('resourcemetadataId', $this->vars) && ($type != 'idea'))
            {
                $this->db->formatConditions(['resourcekeywordResourceId' => $this->vars['resourceId']]);
                $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
                $this->db->orderBy('keywordKeyword');
                $resultset = $this->db->select('resource_keyword', ['resourcekeywordKeywordId', 'keywordKeyword']);
                while ($row = $this->db->fetchRow($resultset))
                {
                    $keywordArray[] = $row['keywordKeyword'];
                }
            }
            $keywordString = isset($keywordArray) ? implode(', ', $keywordArray) : FALSE;
            $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(
                $this->messages->text('resources', 'availableKeywords'),
                'fromKeywords',
                $keywordList,
                6
            ) . BR .
                \HTML\span($this->messages->text("hint", "multiples"), 'hint'));
            $jsonArray = [];
            $jsonArray[] = [
                'startFunction' => 'transferKeyword',
            ];
            $toLeftImage = \AJAX\jActionIcon('toRight', 'onclick', $jsonArray);
            $pString .= \HTML\td($toLeftImage);
            // Entry text area
            $pString .= \HTML\td(\FORM\textareaInput(
                $this->messages->text("resources", "keywords"),
                "keywords",
                \HTML\dbToFormTidy($keywordString),
                50,
                5
            ) . BR .
                \HTML\span($this->messages->text("hint", "keywords"), 'hint') . \HTML\p('&nbsp;'));
        }
        else
        {
            $pString .= \HTML\td(\FORM\textareaInput(
                $this->messages->text("resources", "keywords"),
                "keywords",
                FALSE,
                50,
                5
            ) . BR . \HTML\span(
                $this->messages->text("hint", "keywordsAlt"),
                'hint'
            ) . \HTML\p('&nbsp;'));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();

        return $pString;
    }
    /**
     * write to the database
     *
     * if there is no 'quoteId'/'paraphraseId' input, we are adding a new quote/paraphrase.  Otherwise, editing one.
     *
     * @param string $type is either 'quote' or 'paraphrase'
     *
     * @return string
     */
    public function edit($type)
    {
        $pageStart = 'PageStart';
        $pageEnd = 'PageEnd';
        $paragraph = 'Paragraph';
        $section = 'Section';
        $chapter = 'Chapter';
        $typeText = 'Text';
        $typeComment = 'Comment';
        $summaryType = $type == 'quote' ? 'resourcesummaryQuotes' : 'resourcesummaryParaphrases';
        $userId = $this->session->getVar('setup_UserId');
        // insert
        if (!array_key_exists('resourcemetadataId', $this->vars))
        {
            if (!array_key_exists($typeText, $this->vars) || !trim($this->vars[$typeText]))
            {
                return FALSE;
            }
            $addEdit = 'added';
            $fields[] = 'resourcemetadataResourceId';
            $values[] = $this->vars['resourceId'];
            $fields[] = 'resourcemetadataTimestamp';
            $values[] = $this->db->formatTimestamp();
            if (array_key_exists($pageStart, $this->vars) && $this->vars[$pageStart])
            {
                $fields[] = 'resourcemetadataPageStart';
                $values[] = trim(mb_strtolower($this->vars[$pageStart]));
                if (array_key_exists($pageEnd, $this->vars) && $this->vars[$pageEnd])
                {
                    $fields[] = 'resourcemetadataPageEnd';
                    $values[] = trim(mb_strtolower($this->vars[$pageEnd]));
                }
            }
            if (array_key_exists($paragraph, $this->vars) && $this->vars[$paragraph])
            {
                $fields[] = 'resourcemetadataParagraph';
                $values[] = trim(mb_strtolower($this->vars[$paragraph]));
            }
            if (array_key_exists($section, $this->vars) && $this->vars[$section])
            {
                $fields[] = 'resourcemetadataSection';
                $values[] = trim(mb_strtolower($this->vars[$section]));
            }
            if (array_key_exists($chapter, $this->vars) && $this->vars[$chapter])
            {
                $fields[] = 'resourcemetadataChapter';
                $values[] = trim(mb_strtolower($this->vars[$chapter]));
            }
            $fields[] = 'resourcemetadataText';
            $values[] = trim($this->vars[$typeText]);
            if ($userId)
            {
                $fields[] = 'resourcemetadataAddUserId';
                $values[] = $userId;
            }
            if ($type == 'quote')
            {
                $fields[] = 'resourcemetadataType';
                $values[] = 'q';
            }
            else
            {
                $fields[] = 'resourcemetadataType';
                $values[] = 'p';
            }
            $this->db->insert('resource_metadata', $fields, $values);
            $lastAutoId = $this->db->lastAutoId();
            $this->db->deleteCache('cacheMetadataCreators');
            $this->db->deleteCache('cacheMetadataCollections');
            $this->db->deleteCache('cacheMetadataPublishers');
            $this->summary(1, $summaryType);
            // Write comments table
            if (array_key_exists($typeComment, $this->vars) && $this->vars[$typeComment])
            {
                $this->insertComment($lastAutoId, $userId, $typeComment, $type);
            }
            $this->writeKeywords($lastAutoId, 'resourcekeywordMetadataId');
        }
        // else edit/delete
        else
        {
            // if quoteText is empty, delete the row
            if (!array_key_exists('commentOnly', $this->vars) && !trim($this->vars[$typeText]))
            {
                $addEdit = 'deleted';
                $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_metadata');
                $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_metadata');
                $this->db->formatConditions(['resourcekeywordMetadataId' => $this->vars['resourcemetadataId']]);
                $this->db->delete('resource_keyword');
                $metadataExists = $this->summary(-1, $summaryType);
                // remove cache files for keywords
                $this->db->deleteCache('cacheResourceKeywords');
                $this->db->deleteCache('cacheMetadataKeywords');
                $this->db->deleteCache('cacheQuoteKeywords');
                $this->db->deleteCache('cacheParaphraseKeywords');
                $this->db->deleteCache('cacheMusingKeywords');
                // Remove these cache files if no metadata left in resource
                if (!$metadataExists)
                {
                    $this->db->deleteCache('cacheMetadataCreators');
                    $this->db->deleteCache('cacheMetadataCollections');
                    $this->db->deleteCache('cacheMetadataPublishers');
                }
            }
            else
            {
                $addEdit = 'edited';
                // Quote/paraphrase _text table
                $updateArray = [];
                if (!array_key_exists('commentOnly', $this->vars))
                {
                    $this->db->formatConditions(['resourcemetadataId' => $this->vars['resourcemetadataId']]);
                    $updateArray['resourcemetadataText'] = trim($this->vars[$typeText]);
                    $updateArray['resourcemetadataTimestampEdited'] = $this->db->formatTimestamp();
                    $this->db->update('resource_metadata', $updateArray);
                    $this->writeKeywords($this->vars['resourcemetadataId'], 'resourcekeywordMetadataId');
                }
                // Comment
                $updateArray = [];
                if (array_key_exists($typeComment, $this->vars) && trim($this->vars[$typeComment]))
                {
                    // Is this a new comment or are we editing an old comment?
                    $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]);
                    $this->db->formatConditions(['resourcemetadataAddUserId' => $userId]);
                    $recordset = $this->db->select('resource_metadata', 'resourcemetadataMetadataId');
                    if ($this->db->numRows($recordset))
                    {  // edit existing comment
                        $updateArray['resourcemetadataText'] = trim($this->vars[$typeComment]);
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
                        if ($type == 'quote')
                        {
                            $updateArray['resourcemetadataType'] = 'qc';
                        }
                        else
                        {
                            $updateArray['resourcemetadataType'] = 'pc';
                        }
                        $updateArray['resourcemetadataTimestamp'] = $this->db->formatTimestamp();
                        $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]);
                        $this->db->formatConditions(['resourcemetadataAddUserId' => $userId]);
                        $this->db->update('resource_metadata', $updateArray);
                    }
                    // new comment
                    else
                    {
                        $this->insertComment($this->vars['resourcemetadataId'], $userId, $typeComment, $type);
                    }
                }
                // remove comment row
                else
                {
                    $this->db->formatConditions(['resourcemetadataMetadataId' => $this->vars['resourcemetadataId']]);
                    $this->db->formatConditions(['resourcemetadataAddUserId' => $userId]);
                    $this->db->delete('resource_metadata');
                }
                if (!array_key_exists('commentOnly', $this->vars))
                {
                    // Quote/paraphrase main type table
                    $updateArray = $nulls = [];
                    // page number lowercased in case roman numerals input!
                    if (array_key_exists($pageStart, $this->vars) && $this->vars[$pageStart])
                    {
                        $updateArray['resourcemetadataPageStart'] =
                            trim(mb_strtolower($this->vars[$pageStart]));
                        if (array_key_exists($pageEnd, $this->vars) && $this->vars[$pageEnd])
                        {
                            $updateArray['resourcemetadataPageEnd'] =
                                trim(mb_strtolower($this->vars[$pageEnd]));
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
                    if (array_key_exists($paragraph, $this->vars) && $this->vars[$paragraph])
                    {
                        $updateArray['resourcemetadataParagraph'] = trim($this->vars[$paragraph]);
                    }
                    else
                    {
                        $nulls[] = 'resourcemetadataParagraph';
                    }
                    if (array_key_exists($section, $this->vars) && $this->vars[$section])
                    {
                        $updateArray['resourcemetadataSection'] = trim($this->vars[$section]);
                    }
                    else
                    {
                        $nulls[] = 'resourcemetadataSection';
                    }
                    if (array_key_exists($chapter, $this->vars) && $this->vars[$chapter])
                    {
                        $updateArray['resourcemetadataChapter'] = trim($this->vars[$chapter]);
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
                }
            }
        }
        include_once("core/modules/email/EMAIL.php");
        $emailClass = new EMAIL();
        if (!$emailClass->notify($this->vars['resourceId']))
        {
            $this->badInput->close($this->errors->text("inputError", "mail", GLOBALS::getError()));
        }

        return $addEdit;
    }
    /**
     * update or insert resource_summary
     *
     * @param string $incDec
     * @param string $field
     *
     * @return bool
     */
    public function summary($incDec, $field)
    {
        $metadataExists = TRUE;
        if ($field == 'resourcesummaryQuotes')
        {
            $totalsField = 'databasesummaryTotalQuotes';
        }
        elseif ($field == 'resourcesummaryParaphrases')
        {
            $totalsField = 'databasesummaryTotalParaphrases';
        }
        else
        {
            $totalsField = 'databasesummaryTotalMusings';
        }
        $this->db->formatConditions(['resourcesummaryId' => $this->vars['resourceId']]);
        if ($this->db->numRows($this->db->select('resource_summary', 'resourcesummaryId')))
        { // update
            $this->db->updateSingle('resource_summary', $this->db->formatFields($field) . "=" .
            "COALESCE(" . $this->db->formatFields($field) . "+" .
            $this->db->tidyInput($incDec) . ", " . $this->db->tidyInput($incDec) . ")");
        }
        else
        {  // insert
            $this->db->insert('resource_summary', ['resourcesummaryId', $field], [$this->vars['resourceId'], 1]);
        }
        // if a decrement leaves nothing, delete row.
        $this->db->formatConditions(['resourcesummaryId' => $this->vars['resourceId']]);
        $recordset = $this->db->select(
            ['resource_summary'],
            ['resourcesummaryMusings', 'resourcesummaryParaphrases', 'resourcesummaryQuotes']
        );
        $row = $this->db->fetchRow($recordset);
        if (!$row['resourcesummaryMusings'] && !$row['resourcesummaryParaphrases'] && !$row['resourcesummaryQuotes'])
        {
            $this->db->formatConditions(['resourcesummaryId' => $this->vars['resourceId']]);
            $this->db->delete('resource_summary');
            $metadataExists = FALSE;
        }
        // update master summary table
        $this->db->updateSingle('database_summary', $this->db->formatFields($totalsField) .
            "=" . $this->db->formatFields($totalsField) . "+" . $this->db->tidyInput($incDec));

        return $metadataExists;
    }
    /**
     * Write new keywords to keyword table and gather keyword ids
     *
     * @param mixed $rkId
     * @param mixed $field
     */
    public function writekeywords($rkId, $field)
    {
        $deleteCache = FALSE;
        if ($this->vars['keywords'])
        {
            $kIds = $this->keyword->writeKeywordTable($this->vars);
            if (is_array($kIds))
            {
                // first delete existing rows then insert new ones
                $this->db->formatConditions([$field => $rkId]);
                $this->db->delete('resource_keyword');
                foreach ($kIds as $kId)
                {
                    $this->db->insert('resource_keyword', [$field, 'resourcekeywordKeywordId'], [$rkId, $kId]);
                }
                $deleteCache = TRUE;
            }
        }
        else
        {
            $this->db->formatConditions([$field => $rkId]);
            $resultset = $this->db->select('resource_keyword', $field);
            if ($this->db->numRows($resultset))
            {
                $this->db->formatConditions([$field => $rkId]);
                $this->db->delete('resource_keyword');
                $deleteCache = TRUE;
            }
        }
        if ($deleteCache)
        {
            // remove cache files for keywords
            $this->db->deleteCache('cacheResourceKeywords');
            $this->db->deleteCache('cacheMetadataKeywords');
            $this->db->deleteCache('cacheQuoteKeywords');
            $this->db->deleteCache('cacheParaphraseKeywords');
            $this->db->deleteCache('cacheMusingKeywords');
            $this->db->deleteCache('cacheKeywords');
        }
        $this->keyword->removeHanging();
    }
    /**
     * Insert a comment
     *
     * @param mixed $lastAutoId
     * @param mixed $userId
     * @param mixed $typeComment
     * @param mixed $type
     */
    private function insertComment($lastAutoId, $userId, $typeComment, $type)
    {
        $fields = $values = [];
        $fields[] = 'resourcemetadataMetadataId';
        $values[] = $lastAutoId;
        if ($userId)
        {
            $fields[] = 'resourcemetadataAddUserId';
            $values[] = $userId;
        }
        $fields[] = 'resourcemetadataText';
        $values[] = trim($this->vars[$typeComment]);
        if (array_key_exists('private', $this->vars) && ($this->vars['private'] == 'N'))
        {
            $fields[] = 'resourcemetadataPrivate';
            $values[] = 'N';
        }
        elseif (array_key_exists('private', $this->vars) && (is_numeric($this->vars['private'])))
        {
            $fields[] = 'resourcemetadataPrivate';
            $values[] = $this->vars['private'];
        }
        else
        {
            $fields[] = 'resourcemetadataPrivate';
            $values[] = 'Y';
        }
        if ($type == 'quote')
        {
            $fields[] = 'resourcemetadataType';
            $values[] = 'qc';
        }
        else
        {
            $fields[] = 'resourcemetadataType';
            $values[] = 'pc';
        }
        $fields[] = 'resourcemetadataTimestamp';
        $values[] = $this->db->formatTimestamp();
        $this->db->insert('resource_metadata', $fields, $values);
    }
}
