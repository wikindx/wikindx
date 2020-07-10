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
 * IMPORTENDNOTE: Endnote XML import class
 */
class ENDNOTEIMPORT
{
    private $db;
    private $vars;
    private $session;
    private $parentClass;
    private $tag;
    private $map;
    private $date;
    private $parseCreator;
    private $dirName;
    private $resourceAdded = 0;
    private $resourceDiscarded = 0;
    private $resourceAddedThisRound = 0;
    private $resourceDiscardedThisRound = 0;
    private $creators;
    private $oldTime;
    private $pages;
    private $common;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $fileName;
    private $resourceId;
    private $rejectTitles = [];
    private $entries = [];
    private $entry = [];
    private $entriesLeft = [];
    private $rejects = [];
    private $reject = [];
    private $inputTypes = [];
    private $deleteCacheCreators = FALSE;
    private $deleteCachePublishers = FALSE;
    private $deleteCacheCollections = FALSE;
    private $deleteCacheKeywords = FALSE;
    private $tagId = FALSE;
    private $bibtexStringId = FALSE;
    private $customFields;
    private $unrecognisedFields;
    private $garbageFiles = [];
    private $rIds = [];
    private $errorMessage = FALSE;

    /**
     * Constructor
     *
     * @param mixed $parentClass
     */
    public function __construct($parentClass)
    {
        $this->parentClass = $parentClass;
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->tag = FACTORY_TAG::getInstance();
        include_once("core/messages/PLUGINMESSAGES.php");
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "ENDNOTEMAP.php");
        $this->map = new ENDNOTEMAP();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "ENDNOTEDATEPARSE.php");
        $this->date = new ENDNOTEDATEPARSE();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "ENDNOTECREATORPARSE.php");
        $this->parseCreator = new ENDNOTECREATORPARSE();
        $this->pages = FACTORY_BIBTEXPAGEPARSE::getInstance();
        $this->common = FACTORY_IMPORT::getInstance();
        $this->creators = ['creator1', 'creator2', 'creator3', 'creator4', 'creator5'];
        $this->oldTime = time();
        $this->dirName = WIKINDX_DIR_DATA_FILES;
    }
    /**
     * start the process
     *
     * @param string $message - optional error message
     *
     * @return string
     */
    public function process($message = FALSE)
    {
        // if session variable 'importLock' is TRUE, user is simply reloading this form
        if ($this->session->getVar("importLock")) {
            $this->badInput(HTML\p($this->pluginmessages->text('fileImport'), 'error'));
        }
        $this->fileName = $this->gatherStage1();
        include_once(__DIR__ . DIRECTORY_SEPARATOR . "ENDNOTEPARSEXML.php");
        $parse = new ENDNOTEPARSEXML();
        $entries = $parse->extractEntries($this->fileName);
        if (!$parse->version8) {
            GLOBALS::addTplVar('content', HTML\p($this->pluginmessages->text('importEndnoteNotv8'), 'error'));
            FACTORY_CLOSE::getInstance();
        }
        if (empty($entries)) {
            $this->session->setVar("importLock", TRUE);
            $this->badInput(HTML\p($this->pluginmessages->text('empty'), 'error'));
        }
        $this->version8 = $parse->version8;
        $this->endnoteVersion();
        foreach ($entries as $key => $entry) {
            $this->entry = $this->reject = [];
            if ($this->convertEntries($entry)) {
                $this->entries[$key] = $this->entry;
            }
            if (!empty($this->reject)) {
                $this->rejects[$key] = $this->reject;
            }
        }
        if (empty($this->entries)) {
            $this->session->setVar("importLock", TRUE);
            $this->badInput(HTML\p($this->pluginmessages->text('empty'), 'error'));
        }
        if ($fields = $this->findInvalidFields($entries)) {
            @unlink($this->fileName); // remove garbage - ignore errors
            GLOBALS::addTplVar('content', $fields);

            return;
        }
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerEndnoteImport"));
        $this->entriesLeft = $this->entries;
        $finalInput = $this->writeDb();
        $this->common->collectionDefaults();
        $pString = $this->cleanUp($finalInput);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * stage2Invalid - following on from invalid fields having been found
     *
     * @param string $message - optional error message
     */
    public function stage2Invalid()
    {
        // if session variable 'importLock' is TRUE, user is simply reloading this form
        if ($this->session->getVar("importLock")) {
            $this->badInput($this->errors->text("done", "fileImport"));
        }
        if (!is_file($this->session->getVar("import_FileNameEntries"))) {
            $this->badInput($this->errors->text("file", "read", $this->dirName . DIRECTORY_SEPARATOR .
            $this->session->getVar("import_FileNameEntries")));
        }
        $this->fileName = fopen($this->session->getVar("import_FileNameEntries"), 'r');
        $this->garbageFiles[$this->session->getVar("import_FileNameEntries")] = FALSE;
        if (!feof($this->fileName)) {
            $this->entries = unserialize(base64_decode(trim(fgets($this->fileName))));
        }
        fclose($this->fileName);
        if ($this->session->issetVar("import_Rejects")) {
            $this->rejects = unserialize(base64_decode($this->session->getVar("import_Rejects")));
        } else {
            $this->rejects = [];
        }
        if (empty($this->entries)) {
            $this->session->setVar("importLock", TRUE);
            $this->badInput($this->errors->text("import", "empty"));
        }
        list($error, $this->customFields, $this->unrecognisedFields) = $this->common->getUnrecognisedFields();
        if ($error) {
            $this->badInput($error);
        }
        // NB - we need to write data to database as UTF-8 and parse all bibTeX values for laTeX code
        $this->entriesLeft = $this->entries;
        $finalInput = $this->writeDb();
        $pString = $this->errorMessage ? $this->errorMessage : '';
        $pString .= $this->cleanUp($finalInput);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Continue an import
     */
    public function continueImport()
    {
        // Restore session
        if ($this->session->issetVar("import_RejectTitles")) {
            $this->rejectTitles = unserialize(base64_decode($this->session->getVar("import_RejectTitles")));
        } else {
            $this->rejectTitles = [];
        }
        if ($this->session->issetVar("import_ResourceIds")) {
            $this->rIds = unserialize(base64_decode($this->session->getVar("import_ResourceIds")));
        } else {
            $this->rIds = [];
        }
        // Number added so far
        $this->resourceAdded = $this->session->getVar("import_ResourceAdded");
        // Number discarded so far
        $this->resourceDiscarded = $this->session->getVar("import_ResourceDiscarded");
        // tag ID
        if ($this->session->issetVar("import_TagID")) {
            $this->tagId = $this->session->getVar("import_TagID");
        }
        $this->entriesLeft = $this->entries =
            unserialize(base64_decode($this->session->getVar("import_Entries")));
        $this->garbageFiles = unserialize(base64_decode($this->session->getVar("import_GarbageFiles")));
        if ($this->session->issetVar("import_UnrecognisedFields")) {
            $this->unrecognisedFields = unserialize(base64_decode($this->session->getVar("import_UnrecognisedFields")));
            $this->customFields = unserialize(base64_decode($this->session->getVar("import_CustomFields")));
            $this->vars = unserialize(base64_decode($this->session->getVar("import_ThisVars")));
        }
        $finalInput = $this->writeDb(TRUE);
        $pString = $this->errorMessage ? $this->errorMessage : '';
        $pString .= $this->cleanUp($finalInput);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * find unrecognised field names
     *
     * @return false|string
     */
    private function findInvalidFields()
    {
        $this->invalidFieldNames = [];
        if (!empty($this->inputTypes)) {
            $this->inputTypes = array_unique($this->inputTypes);
        }
        $this->day = $this->month = FALSE;
        foreach ($this->rejects as $reject) {
            foreach ($reject as $field => $value) {
                if (($field == 'source-app') || ($field == 'ref-type') || is_array($value)) {
                    continue;
                }
                if (array_search($field, $this->invalidFieldNames) === FALSE) {
                    $this->invalidFieldNames[] = $field;
                }
            }
        }
        if (!empty($this->rejects)) {
            $this->session->setVar("import_Rejects", base64_encode(serialize($this->rejects)));
        }
        if (!empty($this->invalidFieldNames)) { // prompt to map field names
            list($error, $string) = $this->common->promptFieldNames(
                $this->entries,
                $this->inputTypes,
                $this->map,
                $this->invalidFieldNames,
                FALSE,
                'endnote'
            );
            if ($error) {
                $this->badInput($error);
            } else {
                return $string;
            }
        }

        return FALSE; // continue with import.
    }
    /**
     * Garbage clean up and intermediate session saving when importing in chunks
     *
     * @param mixed $finalInput
     *
     * @return string
     */
    private function cleanUp($finalInput)
    {
        // update total no. resources in summary table
        $recordset = $this->db->select('database_summary', 'databasesummaryTotalResources');
        $totalResources = $this->db->fetchOne($recordset) + $this->resourceAddedThisRound;
        $this->db->update('database_summary', ['databasesummaryTotalResources' => $totalResources]);
        if ($finalInput) {
            $rCommon = FACTORY_RESOURCECOMMON::getInstance();
            $listCommon = FACTORY_LISTCOMMON::getInstance();
            $this->deleteCaches();
            $this->common->tidyTables();
            foreach ($this->garbageFiles as $fileName => $null) {
                unlink($fileName); // remove garbage
            }
            $pString = HTML\p($this->pluginmessages->text("importEndnoteSuccess"), 'success');
            $pString .= HTML\p($this->coremessages->text("import", "added", " " . $this->resourceAdded));
            $pString .= $this->common->printDuplicates($this->resourceDiscarded, $this->rejectTitles);
            $pString .= HTML\hr();
            if (!empty($this->rIds) && (count($this->rIds) <= 50)) {
                $sql = $rCommon->getResource($this->rIds, FALSE, FALSE, FALSE, FALSE, TRUE);
                $listCommon->display($sql, 'list');
            }
            $this->session->delVar("sql_LastMulti");
            $this->session->setVar("importLock", TRUE);
            if ($this->resourceAdded) {
                include_once("core/modules/email/EMAIL.php");
                $email = new EMAIL();
                $email->notify(FALSE, TRUE);
            }
        } else {
            // Store temporary session variables
            // Number added
            $this->session->setVar("import_ResourceAdded", $this->resourceAdded);
            // Number of rejects
            $this->session->setVar("import_ResourceDiscarded", $this->resourceDiscarded);
            // tag ID
            if ($this->tagId) {
                $this->session->setVar("import_TagID", $this->tagId);
            }
            // bibtexString ID
            if ($this->bibtexStringId) {
                $this->session->setVar("import_BibtexStringID", $this->bibtexStringId);
            }
            // Resource IDs
            $this->session->setVar("import_ResourceIds", base64_encode(serialize($this->rIds)));
            // Remaining entries
            $this->session->setVar("import_Entries", base64_encode(serialize($this->entriesLeft)));
            // Rejected titles
            if (!empty($this->rejectTitles)) {
                $this->session->setVar("import_RejectTitles", base64_encode(serialize($this->rejectTitles)));
            }
            // garbage files
            $this->session->setVar("import_GarbageFiles", base64_encode(serialize($this->garbageFiles)));
            // Unrecognised field mapping
            if (isset($this->unrecognisedFields)) {
                $this->session->setVar("import_UnrecognisedFields", base64_encode(serialize($this->unrecognisedFields)));
                // Custom field mapping
                if (isset($this->customFields)) {
                    $this->session->setVar("import_CustomFields", base64_encode(serialize($this->customFields)));
                }
                // $this->vars
                $this->session->setVar("import_ThisVars", base64_encode(serialize($this->vars)));
            }
            $remainder = count($this->entriesLeft);
            $pString = HTML\p($this->coremessages->text(
                "import",
                "executionTimeExceeded",
                ini_get("max_execution_time")
            ));
            $pString .= HTML\p($this->coremessages->text("import", "addedChunk", " " .
                $this->resourceAddedThisRound));
            $pString .= HTML\p("$remainder entries remaining.");
            $pString .= FORM\formHeader("importexportbib_importEndnote");
            $pString .= FORM\hidden('method', 'continueImport');
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Continue")));
            $pString .= FORM\formEnd();
        }

        return $pString;
    }
    /**
     * Delete caches if required.  Must be deleted if various creators, publishers etc. have been added with this import
     */
    private function deleteCaches()
    {
        if ($this->deleteCacheCreators) {
            // remove cache files for creators
            $this->db->deleteCache('cacheResourceCreators');
            $this->db->deleteCache('cacheMetadataCreators');
        }
        if ($this->deleteCachePublishers) {
            // remove cache files for publishers
            $this->db->deleteCache('cacheResourcePublishers');
            $this->db->deleteCache('cacheMetadataPublishers');
            $this->db->deleteCache('cacheConferenceOrganisers');
        }
        if ($this->deleteCacheCollections) {
            // remove cache files for collections
            $this->db->deleteCache('cacheResourceCollections');
            $this->db->deleteCache('cacheMetadataCollections');
            $this->db->deleteCache('cacheResourceCollectionTitles');
            $this->db->deleteCache('cacheResourceCollectionShorts');
        }
        if ($this->deleteCacheKeywords) {
            // remove cache files for keywords
            $this->db->deleteCache('cacheResourceKeywords');
            $this->db->deleteCache('cacheMetadataKeywords');
            $this->db->deleteCache('cacheQuoteKeywords');
            $this->db->deleteCache('cacheParaphraseKeywords');
            $this->db->deleteCache('cacheMusingKeywords');
            $this->db->deleteCache('cacheKeywords');
        }
    }
    /**
     * endnoteVersion - Endnote versions earlier than 8.0 have a _very_ different XML format and
     * type naming to version 8.0 *&^$*&^!  Load appropriate mapping arrays
     */
    private function endnoteVersion()
    {
        if ($this->version8) {
            $this->importTypes = $this->map->importTypes8;
            $this->endnoteXmlFields = $this->map->endnoteXmlFields8;
        } else {
            $this->importTypes = $this->map->importTypesPre8;
            $this->endnoteXmlFields = $this->map->endnoteXmlFieldsPre8;
        }
    }
    /**
     * writeDb - write input to the database
     *
     * @param mixed $continue
     *
     * @return bool
     */
    private function writeDb($continue = FALSE)
    {
        $tagWritten = FALSE;
        if (!$continue) {
            $this->tagId = FALSE;
        }
        $finalInput = TRUE;
        foreach ($this->entries as $key => $entry) {
            $custom = [];
            unset($this->entriesLeft[$key]);
            $this->entry = $entry;
            // If type == 'book' or 'book_article', need to swap 'year1' (WIKINDX's original publication year) and
            // 'year2' (WIKINDX's reprint year)
            if ((($this->entry['type'] == 'book') || ($this->entry['type'] == 'book_article')) &&
                array_key_exists('resource_year', $this->entry) &&
                array_key_exists('resourceyearYear1', $this->entry['resource_year'])
                && array_key_exists('resourceyearYear2', $this->entry['resource_year'])) {
                $year2 = $this->entry['resource_year']['resourceyearYear2'];
                $this->entry['resource_year']['resourceyearYear2'] = $this->entry['resource_year']['resourceyearYear1'];
                $this->entry['resource_year']['resourceyearYear1'] = $year2;
            }
            list($noSort, $title, $subtitle) = $this->common->splitTitle($this->entry['title']);
            if ($this->common->checkDuplicates($noSort, $title, $subtitle, $this->entry['type'])
            ||
            (array_search($this->entry['type'], WIKINDX_DEACTIVATE_RESOURCE_TYPES) !== FALSE)) {
                $rejectTitle = $this->entry['title'] . ".";
                $this->rejectTitles[] = $rejectTitle;
                $this->resourceDiscarded++;
                $this->resourceDiscardedThisRound++;

                continue;
            }
            $this->publisherId = $this->collectionId = FALSE;
            $this->grabDate($this->entry['type']);
            $custom = $this->reject($key);
            $this->resourceId = $this->writeResourceTable($noSort, $title, $subtitle);
            // add any import tag and get tag auto ID.  We write it here after the resource table in case we forbid duplicates and all
            // endnote entries are duplicates - we don't want an empty tag in the WKX_tag table.
            if (!$continue) {
                if (!$tagWritten) {
                    $this->tagId = $this->common->writeTagTable();
                    $tagWritten = TRUE;
                }
            }
            if (array_key_exists('creators', $this->entry)) {
                $creators = [];
                foreach ($this->entry['creators'] as $creatorRole => $creatorRoleArray) {
                    $creatorRoleString = implode(" and ", $creatorRoleArray);
                    $creators[$creatorRole] = $this->parseCreator->parse($creatorRoleString);
                }
            } else {
                $creators = [];
            }
            $this->common->writeCreatorTables($creators);
            $this->writePublisherTable();
            $this->writeCollectionTable();
            $this->writeResourceMiscTable();
            $this->writeResourceYearTable();
            $this->writeResourcePageTable();
            $this->writeResourceKeywordTable();
            if (!empty($custom)) {
                $this->writeResourceCustomTable($custom);
            }
            $this->writeResourceCategoryTable();
            $this->writeResourceTextTable();
            $this->common->writeResourceTimestampTable();
            $this->writeImportRawTable();
            $this->common->writeUserbibliographyresourceTable($this->session->getVar("import_BibId"));
            $this->common->writeBibtexKey();
            $this->resourceAdded++;
            $this->resourceAddedThisRound++;
            // Check we have more than 5 seconds buffer before max_execution_time times out.
            if ((time() - $this->oldTime) >= (ini_get("max_execution_time") - 5)) {
                $finalInput = FALSE;

                break;
            }
        }

        return $finalInput;
    }
    /**
     * reject -- gather rejected fields that wikindx does not recognise for that type and remove from $entry
     *
     * @param mixed $topKey assoc array of one entry for import.
     *
     * $wkType - the WIKINDX resource type for this bibtex entry
     * $rejected - array of rejected field and their values (with bibTeX delimiters added back in)
     * $newEntry - $entry with $rejected elements removed
     *
     * @return array
     */
    private function reject($topKey)
    {
        $custom = [];
        $wkType = $this->entry['type'];
        foreach ($this->rejects[$topKey] as $key => $value) {
            $newEntry = [];
            if (($key == 'bibtexEntryType') ||
            ($key == 'howpublished') || ($key == 'abstract') || ($key == 'keywords')) {
                $newEntry[$key] = $value;

                continue;
            }
            if ($key == 'note') { // Use 'note' in preference to 'annote'
                $newEntry[$key] = $value;

                continue;
            }
            if (($key == 'annote') && !array_key_exists('note', $this->entry)) { // Use 'note' in preference to 'annote'
                $newEntry[$key] = $value;

                continue;
            }
            if (array_search($key, $this->map->{$wkType}['possible']) !== FALSE) {
                if (!array_key_exists($key, $newEntry)) {
                    $newEntry[$key] = $value;
                }
            }
            // Do we map unrecognised fields?
            if (!empty($this->unrecognisedFields) && array_search($key, $this->unrecognisedFields) !== FALSE) {
                $importKey = 'import_' . $key;
                if (array_key_exists($importKey, $this->vars) &&
                    array_search($this->vars[$importKey], $this->map->{$wkType}['possible']) !== FALSE) {
                    // Do unrecognised fields take precedence?
                    if (array_key_exists('import_Precedence', $this->vars)) {
                        $newEntry[$this->vars[$importKey]] = $value;

                        continue;
                    }
                    if (!array_key_exists($this->vars[$importKey], $newEntry)) {
                        $newEntry[$this->vars[$importKey]] = $value;

                        continue;
                    }
                }
            }
            if (array_key_exists($key, $newEntry)) {
                continue;
            }
            if (!empty($this->customFields) && array_key_exists($key, $this->customFields)) {
                $custom[$key] = $value;

                continue;
            }
        }

        return $custom;
    }
    /**
     * elapsedTime()
     *
     * @return int
     */
    private function elapsedTime()
    {
        return round(($this->getMicrotime() - $this->startTime), 5);
    }
    /**
     * get_microtime function adapted from Everett Michaud on Zend.com
     *
     * @return string
     */
    private function getMicrotime()
    {
        $tmp = UTF8::mb_explode(" ", microtime());

        return $tmp[0] + $tmp[1];
    }
    /**
     * writeResourceTable - write WKX_resource table and get lastAutoId
     *
     * @param mixed $noSort
     * @param mixed $title
     * @param mixed $subtitle
     *
     * @return int
     */
    private function writeResourceTable($noSort, $title, $subtitle)
    {
        // If there's nothing saying whether a thesis is a thesis or a dissertation, here we force it to 'thesis'.
        if ($this->entry['type'] == 'thesis') {
            $fields[] = 'resourceField1';
            $values[] = 'thesis';
        }
        $fields[] = 'resourceType';
        $values[] = $this->entry['type'];
        $fields[] = 'resourceTitle';
        $values[] = $title;
        $titleSort = $title;
        if ($noSort) {
            $fields[] = 'resourceNoSort';
            $values[] = $noSort;
        }
        if ($subtitle) {
            $fields[] = 'resourceSubtitle';
            $values[] = $subtitle;
            $titleSort .= ' ' . $subtitle;
        }
        $fields[] = 'resourceTitleSort';
        $values[] = str_replace(['{', '}'], '', $titleSort);
        if (array_key_exists('resource', $this->entry)) {
            foreach ($this->entry['resource'] as $field => $value) {
                if (($this->entry['type'] == 'thesis') && ($field == 'resourceField1')) {
                    continue;
                }
                $fields[] = $field;
                $values[] = $value;
            }
        }

        return $this->common->writeResourceTable($fields, $values);
    }
    /**
     * writeCollectionTable - write WKX_collection table
     */
    private function writeCollectionTable()
    {
        if (!array_key_exists('resource_collection', $this->entry)) {
            return;
        }
        $title = $short = FALSE;
        if (array_key_exists('collectionTitle', $this->entry['resource_collection'])) {
            $title = trim($this->entry['resource_collection']['collectionTitle']);
        }
        if (array_key_exists('collectionTitleShort', $this->entry['resource_collection'])) {
            $short = trim($this->entry['resource_collection']['collectionTitleShort']);
        }
        if (!$title) {
            return;
        }
        $this->collectionId = $this->common->writeCollectionTable($title, $short, $this->entry['type']);
    }
    /**
     * writePublisherTable - write WKX_publisher table
     */
    private function writePublisherTable()
    {
        if (!array_key_exists('resource_publisher', $this->entry)) {
            return;
        }
        $publisherName = $publisherLocation = FALSE;
        if (array_key_exists('publisherName', $this->entry['resource_publisher'])) {
            $publisherName = trim($this->entry['resource_publisher']['publisherName']);
        }
        if (array_key_exists('publisherLocation', $this->entry['resource_publisher'])) {
            $publisherLocation = trim($this->entry['resource_publisher']['publisherLocation']);
        }
        if (!$publisherName) {
            return;
        }
        $this->publisherId = $this->common->writePublisherTable($publisherName, $publisherLocation, $this->entry['type']);
    }
    /**
     * writeResourceMiscTable - write WKX_resource_misc table
     */
    private function writeResourceMiscTable()
    {
        if (array_key_exists('resource_misc', $this->entry)) {
            foreach ($this->entry['resource_misc'] as $field => $value) {
                $fields[] = $field;
                $values[] = $value;
            }
        }
        if ($this->collectionId) {
            $fields[] = 'resourcemiscCollection';
            $values[] = $this->collectionId;
        }
        if ($this->publisherId) {
            $fields[] = 'resourcemiscPublisher';
            $values[] = $this->publisherId;
        }
        if ($this->tagId) {
            $fields[] = 'resourcemiscTag';
            $values[] = $this->tagId;
        }
        $fields[] = 'resourcemiscAddUserIdResource';
        $values[] = $this->session->getVar("setup_UserId");
        $this->common->writeResourcemiscTable($fields, $values);
    }
    /**
     * writeResourceYearTable - write WKX_resource_year table
     */
    private function writeResourceYearTable()
    {
        if (array_key_exists('resource_year', $this->entry)) {
            foreach ($this->entry['resource_year'] as $field => $value) {
                $fields[] = $field;
                $values[] = $value;
            }
        }
        if (!isset($fields)) {
            return;
        }
        $this->common->writeYearTable($fields, $values);
    }
    /**
     * writeResourcePageTable - write WKX_resource_page table
     */
    private function writeResourcePageTable()
    {
        if (!array_key_exists('PageStart', $this->entry)) {
            return;
        }
        $fields[] = 'resourcepagePageStart';
        $values[] = $this->entry['PageStart'];
        if (array_key_exists('PageEnd', $this->entry)) {
            $fields[] = 'resourcepagePageEnd';
            $values[] = $this->entry['PageEnd'];
        }
        if (!isset($fields)) {
            return;
        }
        $this->common->writePageTable($fields, $values);
    }
    /**
     * writeResourceNoteTable - write WKX_resource_note table
     */
    private function writeResourceTextTable()
    {
        $notes = $abstract = $url = FALSE;
        if (array_key_exists('notes', $this->entry)) {
            $notes = $this->entry['notes'];
        }
        if (array_key_exists('abstract', $this->entry)) {
            $abstract = $this->entry['abstract'];
        }
        if (array_key_exists('URLS', $this->entry)) {
            $url = $this->entry['URLS'];
        }
        $this->common->writeResourcetextTable($notes, $abstract, $url);
    }
    /**
     * writeResourceKeywordTable - write WKX_resource_keyword table
     */
    private function writeResourceKeywordTable()
    {
        if (array_key_exists('keywords', $this->entry)) {
            $this->common->writeKeywordTables($this->entry['keywords']);
        }
    }
    /**
     * writeResourceCategoryTable - write WKX_resource_category table
     */
    private function writeResourceCategoryTable()
    {
        if (!$categories = $this->session->getVar("import_Categories")) {
            $categories = 1; // force to 'General' category
        }
        $this->common->writeResourcecategoryTable($categories);
    }
    /**
     * writeImportRawTable - write WKX_import_raw table
     */
    private function writeImportRawTable()
    {
        if (empty($this->reject) || !$this->session->getVar("import_Raw")) {
            return;
        }
        $rejected = [];
        foreach ($this->reject as $key => $value) {
            if (($key == 'source-app') || ($key == 'ref-type')) {
                continue;
            }
            if (array_key_exists($key, $this->endnoteXmlFields)) {
                $rejected[$this->endnoteXmlFields[$key]] = $value;
            }
        }
        $this->common->writeImportrawTable($rejected, FALSE, 'endnote');
    }
    /**
     * writeResourceCustomTable - write WKX_resource_custom table
     *
     * @param mixed $custom assoc array of one entry for import
     */
    private function writeResourceCustomTable($custom)
    {
        if (empty($this->customFields)) {
            return;
        }
        foreach ($this->customFields as $importKey => $id) {
            if (!array_key_exists($importKey, $custom)) {
                continue;
            }
            $this->common->writeResourcecustomTable($custom[$importKey], $id);
        }
    }
    /**
     * grabDate - check for any month field and split into component day/month fields
     *
     * @param string $type WIKINDX resource type
     */
    private function grabDate($type)
    {
        if (array_key_exists('date', $this->entry)) {
            if (!array_key_exists('resource_misc', $this->map->{$type}) or empty($this->map->{$type}['resource_misc'])) {
                return;
            }
            list($month, $day, $year) = $this->date->init($this->entry['date']);
            if (!$year && array_key_exists('year', $this->entry)) { // February 31 or 31 February and no year
                $year = $this->entry['year'];
            }
            if ($month && ($key = array_search('Month', $this->map->{$type}['resource_misc']))) {
                $this->entry['resource_misc'][$key] = $month;
            }
            if ($day && ($key = array_search('Day', $this->map->{$type}['resource_misc']))) {
                $this->entry['resource_misc'][$key] = $day;
            }
            if ($year && array_key_exists('resource_year', $this->map->{$type}) &&
                array_key_exists('resourceyearYear1', $this->map->{$type}['resource_year'])) {
                $this->entry['resource_year']['resourceyearYear1'] = $year;
            }
            if (!$day && !$month && !$year) {
                $this->reject['Date'] = $this->entry['date'];
            }
            unset($this->entry['date']);
        }
    }
    /**
     * gatherStage1 - gather input from stage 1 and return a fullpath filename for parsing.
     *
     * @return string
     */
    private function gatherStage1()
    {
        // a multiple select box so handle as array
        if (isset($this->vars['import_Categories']) && $this->vars['import_Categories']) {
            if (!$this->session->setVar("import_Categories", trim(implode(',', $this->vars['import_Categories'])))) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }
        // bib_Ids is a multiple select box so handle as array
        if (isset($this->vars['import_BibId']) && $this->vars['import_BibId']) {
            if (!$this->session->setVar("import_BibId", trim(implode(',', $this->vars['import_BibId'])))) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }
        if (isset($this->vars['import_Raw']) && $this->vars['import_Raw']) {
            if (!$this->session->setVar("import_Raw", 1)) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }
        if (!$this->session->setVar("import_TitleSubtitleSeparator", $this->vars['import_TitleSubtitleSeparator'])) {
            $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
        }
        if (isset($this->vars['import_Quarantine']) && $this->vars['import_Quarantine']) {
            if (!$this->session->setVar("import_Quarantine", 1)) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }
        if (isset($this->vars['import_ImportDuplicates']) && $this->vars['import_ImportDuplicates']) {
            if (!$this->session->setVar("import_ImportDuplicates", 1)) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }
        if (isset($this->vars['import_KeywordIgnore']) && $this->vars['import_KeywordIgnore']) {
            if (!$this->session->setVar("import_KeywordIgnore", 1)) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }
        // Force to 1 => 'General' group
        if (!$this->session->getVar("import_Categories")) {
            if (!$this->session->setVar("import_Categories", 1)) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }
        if (!isset($_FILES['import_File'])) {
            if ($file = $this->session->getVar("import_File")) {
                return $this->dirName . $file;
            } else {
                $this->badInput(HTML\p($this->pluginmessages->text('upload'), 'error'));
            }
        }
        // Check for file input
        $fileName = \UTILS\uuid();
        if (!move_uploaded_file($_FILES['import_File']['tmp_name'], $this->dirName . DIRECTORY_SEPARATOR . $fileName)) {
            $this->badInput(HTML\p($this->pluginmessages->text('upload'), 'error'));
        }
        if (!$this->session->setVar("import_file", $_FILES['import_File']['name'])) {
            $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
        }
        if ($this->vars['import_Tag']) {
            if (!$tagId = $this->tag->checkExists($this->vars['import_Tag'])) {
                if (!$this->session->setVar("import_Tag", $this->vars['import_Tag'])) {
                    $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
                }
            } else {
                if (!$this->session->setVar("import_TagId", $tagId)) {
                    $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
                }
            }
        } elseif (array_key_exists('import_TagId', $this->vars) && $this->vars['import_TagId']) {
            if (!$this->session->setVar("import_TagId", $this->vars['import_TagId'])) {
                $this->badInput(HTML\p($this->errors->text("sessionError", "write"), 'error'));
            }
        }

        return $this->dirName . DIRECTORY_SEPARATOR . $fileName;
    }
    /**
     * ConvertEntries - convert values to UTF-8 ready for storing in the database, tidy up the array presentation
     * and remove unwanted values.
     *
     * @param string $entry - multidimensional array of one endnote record
     *
     * @return true
     */
    private function convertEntries($entry)
    {
        // Need to grab resource type first
        $type = FALSE;
        $this->inputTypes[] = $entry['ref-type'];
        // Endnote's 'Edited Book' type is WIKINDX's 'book'
        if ($entry['ref-type-name'] == 'Edited Book') {
            $type = $this->entry['type'] = 'book';
        } else {
            $type = $this->entry['type'] = array_search($entry['ref-type-name'], $this->importTypes);
        }
        unset($entry['ref-type']);
        unset($entry['ref-type-name']);
        $this->accessYear = $this->accessDate = FALSE;
        foreach ($entry as $key => $value) {
            $this->extractEntries($key, $value, $type);
        }
        if ($this->accessYear && $this->accessDate) {
            $this->entry['date'] = $this->accessDate . ' ' . $this->accessYear;
        }

        return TRUE;
    }
    /**
     * extractEntries() - parse multi-array extracting values
     *
     * @param array $key - array index
     * @param array $value - array element (array())
     * @param string $type - WIKINDX resource type
     */
    private function extractEntries($key, $value, $type)
    {
        if (is_array($value)) {
            $value = $this->extractFromStyleArray($value);
        }
        $mapped = $pages = $volume = $number = FALSE;
        if ($key == 'pages') {
            $pages = $value;
        }
        if ($key == 'PAGES') {
            $pages = $value;
        }
        if ($pages && array_key_exists('resource_page', $this->map->{$type})) {
            list($this->entry['PageStart'], $this->entry['PageEnd']) = $this->pages->init($pages);

            return;
        }
        // Endnote stores the last update date in pub-dates and access year in volume and access date in number for its
        // Electronic Source.  We want the last two if $type == 'web_article or 'database' and don't want 'pub-dates'.
        if ($key == 'volume') {
            $volume = $value;
        } elseif ($key == 'VOLUME') {
            $volume = $value;
        }
        if ($key == 'number') {
            $number = $value;
        } elseif ($key == 'NUMBER') {
            $number = $value;
        }
        if ($volume && (($type == 'web_article') || ($type == 'database') ||
            ($type == 'web_encyclopedia') || ($type == 'web_encyclopedia_article') || ($type == 'web_site'))) {
            $this->accessYear = trim($volume);

            return;
        }
        if ($number && (($type == 'web_article') || ($type == 'database') ||
            ($type == 'web_encyclopedia') || ($type == 'web_encyclopedia_article') || ($type == 'web_site'))) {
            $this->accessDate = trim($number);

            return;
        }
        foreach ($this->map->$type as $mapTable => $mapArray) {
            if (array_key_exists($key, $this->endnoteXmlFields) &&
                ($mapKey = array_search($this->endnoteXmlFields[$key], $mapArray))) {
                $mapped = TRUE;
                $this->entry[$mapTable][$mapKey] = $value;

                break;
            }
        }
        if (($key == 'abstract') || ($key == 'ABSTRACT') ||
            ($key == 'notes') || ($key == 'NOTES')) {
            $mapped = TRUE;
            $this->entry[$key] = $value;
        }
        if (!$mapped) {
            $this->reject[$key] = $value;
        }
        if ($mapped) {
            return;
        }
        if ($key == 'contributors') {
            $this->extractContributors($value, $type);
        } elseif ($key == 'urls') {
            $this->extractUrl($value, $type);
        } elseif (($key == 'keywords') || ($key == 'KEYWORDS')) {
            $this->extractKeywords($value, $type);
        } elseif (is_array($value)) {
            $this->extractSecondOrder($value, $type);
        }
    }
    /**
     * extractFromStyleArray()
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function extractFromStyleArray($value)
    {
        if (is_array($value) && @array_shift(array_keys($value)) == 'style') {
            return $value['style'][0];
        } else {
            return $value;
        }
    }
    /**
     * extractSecondOrder() - 3D arrays
     *
     * @param array $array array of arrays
     * @param string $type WIKINDX resource type
     */
    private function extractSecondOrder($array, $type)
    {
        foreach ($array as $key => $value) {
            if ($key == 'title') {
                $this->entry['title'] = $this->extractFromStyleArray($value[0]);

                continue;
            }
            // Endnote stores a patent's international author in the tertiary-title field and the international title
            // in the tertiary-author field.  Why I ask you?
            if (($type == 'patent') && ($key == 'tertiary-title') && ($mapKey = array_search(
                $this->endnoteXmlFields[$key],
                $this->map->{$type}['resource_creator']
            ))) {
                $this->entry[$mapKey][] = $this->extractFromStyleArray($value[0]);

                continue;
            }
            $mapped = FALSE;
            foreach ($this->map->$type as $mapTable => $mapArray) {
                if (array_key_exists($key, $this->endnoteXmlFields) &&
                    ($mapKey = array_search($this->endnoteXmlFields[$key], $mapArray))) {
                    $mapped = TRUE;
                    $this->entry[$mapTable][$mapKey] = $this->extractFromStyleArray($value[0]);

                    break;
                }
            }
            if (!$mapped) {
                $this->reject[$key] = $this->extractFromStyleArray($value[0]);
            }
            // Endnote stores the last update date in pub-dates and access year in volume and access date in number for its
            // Electronic Source.  We want the last two if $type == 'web_article or 'database' and don't want 'pub-dates'.
            if ($key == 'pub-dates') {
                if (($type != 'web_article') && ($type != 'database') &&
                    ($type != 'web_site') && ($type != 'web_encyclopedia') && ($type != 'web_encyclopedia_article')) {
                    foreach ($value[0] as $dateKey => $dateValue) {
                        if ($dateKey == 'date') {
                            $this->entry['date'] = $this->extractFromStyleArray($dateValue[0]);

                            continue;
                        }
                        $this->reject[$dateKey] = $this->extractFromStyleArray($dateValue[0]);
                    }
                }
            }
        }
    }
    /**
     * extractKeywords()
     *
     * @param array $array array of arrays
     * @param string $type WIKINDX resource type
     */
    private function extractKeywords($array, $type)
    {
        foreach ($array['keyword'] as $value) {
            $keyword = $this->extractFromStyleArray($value);
            if (!array_key_exists('keywords', $this->entry) || (array_search($keyword, $this->entry['keywords']) === FALSE)) {
                $this->entry['keywords'][] = $keyword;
            }
        }
    }
    /**
     * extractContributors()
     *
     * @param array$array array of contributors
     * @param string $type WIKINDX resource type
     */
    private function extractContributors($array, $type)
    {
        foreach ($array as $aKey => $value) {
            foreach ($value as $aValue) {
                foreach ($aValue as $authorKey => $authorValue) {
                    if (array_key_exists($aKey, $this->endnoteXmlFields) &&
                        ($mapKey = array_search($this->endnoteXmlFields[$aKey], $this->map->{$type}['resource_creator']))) {
                        foreach ($authorValue as $authorElement) {
                            $this->entry['creators'][$mapKey][] = $this->extractFromStyleArray($authorElement);
                        }

                        continue;
                    }
                    // Endnote stores a patent's international author in the tertiary-title field and the international title
                    // in the tertiary-author field.  Why I ask you?
                    if (($type == 'patent') &&
                        ($mapKey = array_search($this->endnoteXmlFields[$authorKey], $this->map->{$type}['resource'])) &&
                        ($authorKey == 'tertiary-authors')) {
                        $this->entry['resource'][$mapKey] = $this->extractFromStyleArray($authorValue[0]);

                        continue;
                    }
                    $this->reject[$authorKey] = $this->extractFromStyleArray($authorValue[0]);
                }
            }
        }
    }
    /**
     * extractUrl()
     *
     * @param array$array array of urls
     * @param string $type WIKINDX resource type
     */
    private function extractUrl($array, $type)
    {
        if (!is_array($array)) {
            return;
        }
        foreach ($array as $key => $value) {
            if ($key == 'related-urls') {
                $uArray = [];
                foreach ($value[0] as $urlKey => $urlValue) {
                    if (($urlKey == 'url') && array_key_exists($urlKey, $this->endnoteXmlFields) &&
                        (FALSE !== array_search($this->endnoteXmlFields[$urlKey], $this->map->{$type}['resource']))) {
                        $uArray[] = $this->extractFromStyleArray($urlValue[0]);
                    }
                }
                if (!empty($uArray)) {
                    $this->entry['URLS'] = $uArray;
                } else {
                    $this->reject[$key] = $value;
                }
            }
        }
    }
    /**
     * bad Input function
     *
     * @param mixed $error
     */
    private function badInput($error)
    {
        echo $error;
        $this->parentClass->initEndnoteImport($error);
        FACTORY_CLOSE::getInstance();
    }
}
