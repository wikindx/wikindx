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
 * DELETERESOURCE class
 *
 * Delete resources
 */
class DELETERESOURCE
{
    public $resourceIds = [];
    public $navigate = FALSE;
    public $nextResourceId;
    private $db;
    private $vars;
    private $messages;
    private $errors;
    private $success;
    private $session;
    private $badInput;
    private $gatekeep;
    private $deleteType = 'resource';
    private $idsRaw;
    private $checkPublishers = [];
    private $checkConfPublishers = [];
    private $checkCollections = [];
    private $checkTags = [];

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();


        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
    }
    /**
     * check we are allowed to delete and load appropriate method
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->requireSuper = FALSE; // only admins can delete resources if set to TRUE
        $this->gatekeep->init();
        if (array_key_exists('function', $this->vars)) {
            $function = $this->vars['function'];
            $this->{$function}();
        } else {
            $this->display();
        }
    }
    /**
     * Ask for confirmation of delete resource
     * 
     * @param bool $deleteWithinList default FALSE
     */
    public function deleteResourceConfirm($deleteWithinList = FALSE)
    {
        if (!$this->validateInput()) {
            $this->display($this->errors->text("inputError", "missing"));
            FACTORY_CLOSE::getInstance();
        }
        if ($this->deleteType == 'tag') {
            $this->vars['resource_id'] = $this->collectResourceFromTag();
        }
        $res = FACTORY_RESOURCECOMMON::getInstance();
        if (is_array($this->vars['resource_id'])) {
            $maxSize = ini_get('max_input_vars');
            $size = 0;
            foreach ($this->vars as $var) {
                if (is_array($var)) {
                    $size += count($var);
                } else {
                    ++$size;
                }
            }
            if ($size >= $maxSize) {
                $this->display($this->errors->text("inputError", "maxInputVars", "$maxSize"));
                FACTORY_CLOSE::getInstance();
            }
            $this->db->formatConditionsOneField($this->vars['resource_id'], 'resourceId');
        } else {
            $this->db->formatConditions(['resourceId' => $this->vars['resource_id']]);
        }
        $recordset = $res->getResource(FALSE, $this->db->formatFields('creatorSurname'));
        if (!$numDeletes = $this->db->numRows($recordset)) {
            $this->display($this->messages->text("resources", "noResult"));
            FACTORY_CLOSE::getInstance();
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete"));
        // Rather than print 100s or 1000s of resources, we limit display to <= 50
        if ($numDeletes <= 50) {
            $resourceList = [];
            $bibStyle = FACTORY_BIBSTYLE::getInstance();
            $bibStyle->output = 'html';
            while ($row = $this->db->fetchRow($recordset)) {
                $resourceList[]['resource'] = $bibStyle->process($row);
            }
            // Templates expect list ordered from 0, so we renumber from zero
            $rL = array_values($resourceList);
            GLOBALS::setTplVar('resourceList', $rL);
            GLOBALS::addTplVar('submit', \FORM\formSubmit($this->messages->text("submit", "Delete")) . \FORM\formEnd());
            unset($resourceList, $rL);
            $pString = '';
        } else {
            $pString = $this->messages->text("misc", "confirmDelete", " " . $numDeletes . " ");
        }
        $pString .= \FORM\formHeader('admin_DELETERESOURCE_CORE');
        $pString .= \FORM\hidden('function', 'process');
        $pString .= \FORM\hidden('deleteWithinList', $deleteWithinList);
        if ($this->navigate) {
            $pString .= \FORM\hidden('navigate', $this->navigate);
        }
        if ($this->nextResourceId) {
            $pString .= \FORM\hidden('nextResourceId', $this->nextResourceId);
        }
        if (is_array($this->vars['resource_id'])) {
            $pString .= \FORM\hidden("resource_id", implode(",", $this->vars['resource_id']));
        } else {
            $pString .= \FORM\hidden("resource_id", $this->vars['resource_id']);
        }
        if (array_key_exists('nextDelete', $this->vars)) {
            $pString .= \FORM\hidden("nextDelete", $this->vars['nextDelete']);
        }
        $pString .= BR . "&nbsp;" . BR;
        if ($numDeletes > 50) {
            $pString .= \FORM\formSubmit($this->messages->text("submit", "Delete")) . \FORM\formEnd();
        }
        $this->session->setVar("deleteResourceLock", FALSE);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * display select box of resources to delete
     *
     * @param false|string $message
     */
    private function display($message = FALSE)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "delete"));
        if (!$this->resources = $this->grabAll()) {
            GLOBALS::addTplVar('content', $this->messages->text('misc', 'noResources'));

            return;
        }
        include_once("core/miscellaneous/TAG.php");
        $tag = new TAG();
        $tags = $tag->grabAll();
        $pString = $message ? $message : FALSE;
        $pString .= \FORM\formHeader('admin_DELETERESOURCE_CORE');
        $pString .= \FORM\hidden('function', 'deleteResourceConfirm');
        $pString .= \HTML\tableStart();
        $pString .= \HTML\trStart();
        $pString .= \HTML\td(\FORM\selectFBoxValueMultiple(FALSE, "resource_id", $this->resources, 20, 80) .
            BR . \HTML\span($this->messages->text("hint", "multiples"), 'hint') . BR .
            BR . \FORM\formSubmit($this->messages->text("submit", "Confirm")));
        if (is_array($tags)) {
            // add 0 => IGNORE to tags array
            $temp[0] = $this->messages->text("misc", "ignore");
            foreach ($tags as $key => $value) {
                $temp[$key] = $value;
            }
            $tags = $temp;
            $pString .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text("misc", "tag"), 'bibtex_tagId', $tags, 5) .
            BR . $this->messages->text('hint', 'multiples'));
        }
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * process
     */
    private function process()
    {
        // redeleting an already deleted resource?
        if ($this->session->getVar("deleteResourceLock")) {
            $this->display($this->errors->text('done', 'resource'));
            FACTORY_CLOSE::getInstance();
        }
        if (!$this->validateInput()) {
            $this->display($this->errors->text("inputError", "missing"));
            FACTORY_CLOSE::getInstance();
        }
        $this->idsRaw = UTF8::mb_explode(',', $this->vars['resource_id']);
        $this->reallyDelete();
        $this->checkHanging();
        $this->resetSummary();
        // If we have 0 resources left, remove 'sql_stmt' etc. from session so it doesn't cause problems with
        // exporting bibliographies etc.
        if (!$this->db->selectFirstField('database_summary', 'databasesummaryTotalResources')) {
            $this->session->delVar("sql_ListStmt");
            $this->session->delVar("sql_LastMulti");
            $this->session->delVar("sql_LastSolo");
        }
        $pString = $this->success->text("resourceDelete");
        // Lock reload.
        $this->session->setVar("deleteResourceLock", TRUE);
        if ($this->vars['deleteWithinList']) { // i.e. from the organize list select box – need to recalculate list total we return to.
        	$this->session->setVar("setup_PagingTotal", $this->session->getVar("setup_PagingTotal") - count($this->idsRaw));
        	$this->session->delVar("list_PagingAlphaLinks");
        }
        // Which page do we return to?
        if ($this->session->getVar("setup_PagingTotal") == 0) {
            include_once("core/display/FRONT.php");
            $front = new FRONT($pString); // __construct() runs on autopilot
        }
        elseif ($this->navigate == 'nextResource') { // next single view
            $navigate = FACTORY_NAVIGATE::getInstance();
            $navigate->resource($this->nextResourceId, $pString);
        } elseif ($this->navigate == 'list') { // previous multi list
            $navigate = FACTORY_NAVIGATE::getInstance();
            $navigate->listView($pString);
        } elseif ($this->navigate == 'front') { // Return to home page
            include_once("core/display/FRONT.php");
            $front = new FRONT($pString); // __construct() runs on autopilot
        } else {
            $this->display($pString); // return to multiple resource delete page -- $this->navigate == FALSE
        }
        FACTORY_CLOSE::getInstance();
    }
    /**
     * delete resources and meta data
     */
    private function reallyDelete()
    {
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceId');
        $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
        $recordset = $this->db->select('resource', ['resourcemiscPublisher', 'resourcemiscCollection',
            'resourcemiscTag', 'resourceId', 'resourceType', 'resourcemiscField1', ]);
        while ($row = $this->db->fetchRow($recordset)) {
            if ($row['resourcemiscPublisher']) {
                $this->checkPublishers[$row['resourcemiscPublisher']] = FALSE;
            }
            if (($row['resourceType'] == 'proceedings_article') && $row['resourcemiscField1']) {
                $this->checkConfPublishers[$row['resourcemiscField1']] = FALSE;
            }
            if ($row['resourcemiscCollection']) {
                $this->checkCollections[$row['resourcemiscCollection']] = FALSE;
            }
            if ($row['resourcemiscTag']) {
                $this->checkTags[$row['resourcemiscTag']] = FALSE;
            }
        }
        // now start delete
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceId');
        $this->db->delete('resource');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcemiscId');
        $this->db->delete('resource_misc');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcetextId');
        $this->db->delete('resource_text');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcecreatorResourceId');
        $this->db->delete('resource_creator');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcekeywordResourceId');
        $this->db->delete('resource_keyword');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceusertagsResourceId');
        $this->db->delete('resource_user_tags');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcecategoryResourceId');
        $this->db->delete('resource_category');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcepageId');
        $this->db->delete('resource_page');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcesummaryId');
        $this->db->delete('resource_summary');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcetimestampId');
        $this->db->delete('resource_timestamp');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceyearId');
        $this->db->delete('resource_year');
        $this->db->formatConditionsOneField($this->idsRaw, 'importrawId');
        $this->db->delete('import_raw');
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcecustomResourceId');
        $this->db->delete('resource_custom');
        $this->db->formatConditionsOneField($this->idsRaw, 'statisticsresourceviewsResourceId');
        $this->db->delete('statistics_resource_views');
        $this->db->formatConditionsOneField($this->idsRaw, 'statisticsattachmentdownloadsResourceId');
        $this->db->delete('statistics_attachment_downloads');
        $this->deleteMetadata();
        $this->checkBibtexStringTable();
        // delete these ids from any user bibliographies
        $this->db->formatConditionsOneField($this->idsRaw, 'userbibliographyresourceResourceId');
        $this->db->delete('user_bibliography_resource');
        // check file attachments
        $this->db->formatConditionsOneField($this->idsRaw, 'resourceattachmentsResourceId');
        $recordSet = $this->db->select(
            'resource_attachments',
            ['resourceattachmentsId', 'resourceattachmentsHashFilename']
        );
        while ($row = $this->db->fetchRow($recordSet)) {
            $hashes[$row['resourceattachmentsId']] = $row['resourceattachmentsHashFilename'];
        }
        if (isset($hashes)) {
            foreach ($hashes as $id => $hash) {
                $this->db->formatConditions(['resourceattachmentsId' => $id]);
                $this->db->delete('resource_attachments');
                // Is file used by other resources?  If not, unlink it
                $this->db->formatConditions(['resourceattachmentsHashFilename' => $hash]);
                $recordSet = $this->db->select('resource_attachments', 'resourceattachmentsHashFilename');
                if (!$this->db->numRows($recordSet)) { // Unlink it
                    @unlink(WIKINDX_DIR_DATA_ATTACHMENTS . DIRECTORY_SEPARATOR . $hash);
                }
            }
        }
    }
    /**
     * decrement summary table
     */
    private function resetSummary()
    {
        $num = $this->db->numRows($this->db->select('resource', 'resourceId'));
        $this->db->update('database_summary', ['databasesummaryTotalResources' => $num]);
        $this->db->formatConditions(['resourcemetadataType' => 'p']);
        $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
        $this->db->update('database_summary', ['databasesummaryTotalParaphrases' => $num]);
        $this->db->formatConditions(['resourcemetadataType' => 'q']);
        $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
        $this->db->update('database_summary', ['databasesummaryTotalQuotes' => $num]);
        $this->db->formatConditions(['resourcemetadataType' => 'm']);
        $num = $this->db->numRows($this->db->select('resource_metadata', 'resourcemetadataId'));
        $this->db->update('database_summary', ['databasesummaryTotalMusings' => $num]);
    }
    /**
     * checkHanging
     *
     * check that delete of resources hasn't left any resource-less creators, keywords, publisher, collections etc.
     * If so, delete them.
     */
    private function checkHanging()
    {
        $creator = FACTORY_CREATOR::getInstance();
        $keyword = FACTORY_KEYWORD::getInstance();
        $publisher = FACTORY_PUBLISHER::getInstance();
        // Collections -- must be done first as they might contain publishers and creators
        if (!empty($this->checkCollections)) {
            $removeCollections = $this->checkCollections;
            foreach ($this->checkCollections as $collectionId => $null) {
                $this->db->formatConditions(['resourcemiscCollection' => $collectionId]);
                $recordset = $this->db->select('resource_misc', 'resourcemiscCollection');
                if ($this->db->numRows($recordset)) {
                    unset($removeCollections[$collectionId]);
                }
            }
            if (!empty($removeCollections)) {
                $this->db->formatConditionsOneField(array_keys($removeCollections), 'collectionId');
                $this->db->delete('collection');
                $this->db->deleteCache('cacheResourceCollections');
                $this->db->deleteCache('cacheResourceCollectionTitles');
                $this->db->deleteCache('cacheResourceCollectionShorts');
                $this->db->deleteCache('cacheMetadataCollections');
            }
        }
        // Creators
        $creator->removeHanging();
        // Keywords
        $keyword->removeHanging();
        // Publishers
        $publisher->removeHanging();
        // Tags
        foreach ($this->checkTags as $tagId => $void) {
            $this->db->formatConditions(['resourcemiscTag' => $tagId]);
            if (!$this->db->selectFirstField('resource_misc', 'resourcemiscTag')) {
                $this->db->formatConditions(['tagId' => $tagId]);
                $this->db->delete('tag');
            }
        }
    }
    /**
     * check @strings still have resources in import_raw - else delete string entries
     */
    private function checkBibtexStringTable()
    {
        $recordset = $this->db->select('import_raw', ['importrawId', 'importrawStringId']);
        // Delete all from `bibtex_string`
        if (!$this->db->numRows($recordset)) {
            $this->db->delete('bibtex_string');

            return;
        }
        $rawStringIds = [];
        while ($row = $this->db->fetchRow($recordset)) {
            if (!$row['importrawStringId']) {
                continue;
            }
            $rawStringIds[] = $row['importrawStringId'];
        }
        if (empty($rawStringIds)) {
            return;
        }
        foreach (array_unique($rawStringIds) as $id) {
            $deleteIds['bibtexstringId'] = $id;
        }
        $this->db->formatConditions($deleteIds, TRUE); // not equal to...
        $this->db->delete('bibtex_string');
    }
    /**
     * validate input
     *
     * @return bool
     */
    private function validateInput()
    {
        if (array_key_exists('navigate', $this->vars)) {
            $this->navigate = $this->vars['navigate'];
        }
        if (array_key_exists('nextResourceId', $this->vars)) {
            $this->nextResourceId = $this->vars['nextResourceId'];
        }
        if (!empty($this->resourceIds)) {
            $this->vars = array_merge($this->vars, $this->resourceIds);
        }
        if (array_key_exists('bibtex_tagId', $this->vars)) {
            foreach ($this->vars['bibtex_tagId'] as $tag) {
                if ($tag) {
                    $this->deleteType = 'tag';

                    return TRUE;
                }
            }
        }

        return array_key_exists('resource_id', $this->vars);
    }
    /**
     * run SQL delete statements on meta data
     */
    private function deleteMetadata()
    {
        $ids = [];
        // First get meta data ids for deleting from resource_keyword
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcemetadataResourceId');
        $recordset = $this->db->select('resource_metadata', 'resourcemetadataId');
        while ($row = $this->db->fetchRow($recordset)) {
            $ids[] = $row['resourcemetadataId'];
        }
        // Delete meta data parent row
        $this->db->formatConditionsOneField($this->idsRaw, 'resourcemetadataResourceId');
        $this->db->delete('resource_metadata');
        if (empty($ids)) {
            return;
        }
        // Delete any quote or paraphrase comments
        $this->db->formatConditionsOneField($ids, 'resourcemetadataMetadataId');
        $this->db->delete('resource_metadata');
        // Delete metadata keywords
        $this->db->formatConditionsOneField($ids, 'resourcekeywordMetadataId');
        $this->db->delete('resource_keyword');
    }
    /**
     * Grab ids and titles of resources
     *
     * @retrun array
     */
    private function grabAll()
    {
        $titles = [];
        $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourceId');
        $this->db->leftJoin('creator', 'creatorId', 'resourcecreatorCreatorMain');
        $this->db->leftJoin('resource_year', 'resourceyearId', 'resourceId');
        $fields[] = $this->db->formatFields(['resourceId', 'resourceType', 'resourceTitleSort', 'creatorSurname']);
        $fields[] = $this->db->coalesce(['resourceyearYear1', 'resourceyearYear2'], 'year');
        $this->db->groupBy(['resourceId', 'resourceType', 'resourceTitleSort', 'creatorSurname', 'year']);
        $this->db->orderBy('creatorSurname');
        $this->db->orderBy('year');
        $this->db->orderBy('resourceTitleSort', TRUE, FALSE);
        $recordset = $this->db->select('resource', implode(',', $fields), FALSE, FALSE);
        while ($row = $this->db->fetchRow($recordset)) {
            $final = [];
            if ($row['creatorSurname']) {
                $final[] = $row['creatorSurname'];
            }
            if ($row['year']) {
                $final[] = '(' . $row['year'] . ')';
            }
            $final[] = $row['resourceTitleSort'];
            $final[] = '[' . $row['resourceType'] . ']';
            $titles[$row['resourceId']] = \HTML\dbToFormTidy(implode(', ', $final));
        }

        return $titles;
    }
    /**
     * get array of resource ids belonging to tag ids
     *
     * @return array
     */
    private function collectResourceFromTag()
    {
        $this->db->formatConditionsOneField($this->vars['bibtex_tagId'], 'resourcemiscTag');
        $recordset = $this->db->select('resource_misc', 'resourcemiscId');
        if (!$this->db->numRows($recordset)) {
            $this->display($this->messages->text("resources", "noResult"));
            FACTORY_CLOSE::getInstance();
        }
        while ($row = $this->db->fetchRow($recordset)) {
            $ids[] = $row['resourcemiscId'];
        }

        return $ids;
    }
}
