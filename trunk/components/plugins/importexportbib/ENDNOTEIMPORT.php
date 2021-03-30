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
    private $badInput;
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
    private $formData = [];

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
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->tag = FACTORY_TAG::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->pluginmessages = new PLUGINMESSAGES('importexportbib', 'importexportbibMessages');
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "ENDNOTEMAP.php"]));
        $this->map = new ENDNOTEMAP();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "ENDNOTEDATEPARSE.php"]));
        $this->date = new ENDNOTEDATEPARSE();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "ENDNOTECREATORPARSE.php"]));
        $this->parseCreator = new ENDNOTECREATORPARSE();
        $this->pages = FACTORY_BIBTEXPAGEPARSE::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "modules", "import", "IMPORTCOMMON.php"]));
        $this->common = new IMPORTCOMMON();
        $this->common->importType = 'endnote';
        $this->creators = ['creator1', 'creator2', 'creator3', 'creator4', 'creator5'];
        $this->oldTime = time();
        $this->dirName = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]);
        GLOBALS::setTplVar('heading', $this->pluginmessages->text("headerImportEndnote"));
    }
    /**
     * start the process
     */
    public function process()
    {
        $this->fileName = $this->gatherStage1();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "ENDNOTEPARSEXML.php"]));
        $parse = new ENDNOTEPARSEXML();
        $entries = $parse->extractEntries($this->fileName);
        if (!$parse->version8)
        {
            $this->badInput(HTML\p($this->pluginmessages->text('importEndnoteNotv8'), 'error'));
        }
        if (empty($entries))
        {
            $this->badInput(HTML\p($this->pluginmessages->text('empty'), 'error'));
        }
        $this->version8 = $parse->version8;
        $this->endnoteVersion();
        foreach ($entries as $key => $entry)
        {
            $this->entry = $this->reject = [];
            if ($this->convertEntries($entry))
            {
                $this->entries[$key] = $this->entry;
            }
            if (!empty($this->reject))
            {
                $this->rejects[$key] = $this->reject;
            }
        }
        if (empty($this->entries))
        {
            $this->badInput(HTML\p($this->pluginmessages->text('empty'), 'error'));
        }
        $this->formData['import_Rejects'] = $this->rejects;
        $this->findInvalidFields($entries);
        $this->entriesLeft = $this->entries;
        $finalInput = $this->writeDb();
        $this->common->collectionDefaults();
        $this->cleanUp($finalInput);
    }
    /**
     * stage2Invalid - following on from invalid fields having been found
     */
    public function stage2Invalid()
    {
        $this->formData = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
        $this->rejects = $this->formData['import_Rejects'];
        \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        if (!is_file($this->formData["import_FileNameEntries"]))
        {
            $this->badInput->close($this->errors->text("file", "read", implode(DIRECTORY_SEPARATOR, [$this->dirName,
                $this->formData["import_FileNameEntries"], ])), $this->badClass, $this->badFunction);
        }
        $this->garbageFiles[$this->formData["import_FileNameEntries"]] = FALSE;
        if ($this->fileName = fopen($this->formData["import_FileNameEntries"], 'r'))
        {
            if (!feof($this->fileName))
            {
                $this->entries = unserialize(base64_decode(trim(fgets($this->fileName))));
            }
            fclose($this->fileName);
        }
        if (empty($this->entries))
        {
            $this->badInput($this->errors->text("import", "empty"));
        }
        list($error, $this->customFields, $this->unrecognisedFields) = $this->common->getUnrecognisedFields($this->formData);
        if ($error)
        {
            $this->badInput($error);
        }
        // NB - we need to write data to database as UTF-8 and parse all bibTeX values for laTeX code
        $this->entriesLeft = $this->entries;
        $finalInput = $this->writeDb();
        $this->cleanUp($finalInput);
    }
    /**
     * Continue an import
     */
    public function continueImport()
    {
        $data = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
        if (array_key_exists("import_RejectTitles", $data))
        {
            $this->rejectTitles = $data["import_RejectTitles"];
        }
        if (array_key_exists("import_Rejects", $data))
        {
            $this->rejects = $data["import_Rejects"];
        }
        $this->rIds = $data["import_ResourceIds"];
        // Number added so far
        $this->resourceAdded = $data["import_ResourceAdded"];
        // Number discarded so far
        $this->resourceDiscarded = $data["import_ResourceDiscarded"];
        // tag ID
        if (array_key_exists("import_TagId", $data))
        {
            $this->tagId = $data["import_TagId"];
        }
        // bibtexString ID
        if (array_key_exists("import_BibtexStringId", $data))
        {
            $this->bibtexStringId = $data["import_BibtexStringId"];
        }
        $this->entriesLeft = $this->entries = $data["import_Entries"];
        $this->garbageFiles = $data["import_GarbageFiles"];
        if (array_key_exists("import_UnrecognisedFields", $data))
        {
            $this->unrecognisedFields = $data["import_UnrecognisedFields"];
            if (array_key_exists("import_CustomFields", $data))
            {
                $this->customFields = $data["import_CustomFields"];
            }
        }
        \TEMPSTORAGE\delete($this->db, $this->vars['uuid']);
        $this->vars = $data["import_ThisVars"];
        $finalInput = $this->writeDb(TRUE);
        $this->cleanUp($finalInput);
    }
    /**
     * find unrecognised field names
     *
     * @return false|string
     */
    private function findInvalidFields()
    {
        $this->invalidFieldNames = [];
        if (!empty($this->inputTypes))
        {
            $this->inputTypes = array_unique($this->inputTypes);
        }
        $this->day = $this->month = FALSE;
        foreach ($this->rejects as $reject)
        {
            foreach ($reject as $field => $value)
            {
                if (($field == 'source-app') || ($field == 'ref-type') || is_array($value))
                {
                    continue;
                }
                if (array_search($field, $this->invalidFieldNames) === FALSE)
                {
                    $this->invalidFieldNames[] = $field;
                }
            }
        }
        // Can only map to custom fields – check there are some. . .
        $recordset = $this->db->select('custom', ['customId', 'customLabel']);
        if (!empty($this->invalidFieldNames) && $this->db->numRows($recordset))
        { // prompt to map field names
            list($error, $pString, $uuid) = $this->common->promptFieldNames(
                $this->entries,
                $this->inputTypes,
                $this->map,
                $this->invalidFieldNames,
                $this->formData
            );
            if ($error)
            {
                $this->badInput($error);
            }
            else
            {
                @unlink($this->fileName); // remove garbage - ignore errors
                \TEMPSTORAGE\store($this->db, $uuid, ['form' => $pString, 'heading' => $this->pluginmessages->text("headerImportEndnote")]);
                header("Location: index.php?action=import_IMPORTCOMMON_CORE&method=importInvalidFields&uuid=$uuid");
                die;
            }
        }

        return FALSE; // continue with import.
    }
    /**
     * Garbage clean up and intermediate session saving when importing in chunks
     *
     * @param mixed $finalInput
     */
    private function cleanUp($finalInput)
    {
        $uuid = \TEMPSTORAGE\getUuid($this->db);
        if ($finalInput)
        {
            $message = \HTML\p($this->pluginmessages->text("importEndnoteSuccess"), 'success', 'center');
            \TEMPSTORAGE\store(
                $this->db,
                $uuid,
                ['rIds' => $this->rIds, 'resourceAdded' => $this->resourceAdded, 'garbageFiles' => $this->garbageFiles,
                    'resourceDiscarded' => $this->resourceDiscarded, 'rejectTitles' => $this->rejectTitles, 'rejects' => $this->rejects,
                    'deleteCacheCreators' => $this->deleteCacheCreators, 'deleteCachePublishers' => $this->deleteCachePublishers,
                    'deleteCacheCollections' => $this->deleteCacheCollections, 'deleteCacheKeywords' => $this->deleteCacheKeywords, 
                    'importMessages' => $message]
            );
            header("Location: index.php?action=import_IMPORTCOMMON_CORE&method=importSuccess&uuid=$uuid");
            die;
        }
        else
        {
            // Number added
            $tsArray["import_ResourceAdded"] = $this->resourceAdded;
            // Number of rejects
            $tsArray["import_ResourceDiscarded"] = $this->resourceDiscarded;
            // tag ID
            if (isset($this->tagId))
            {
                $tsArray["import_TagId"] = $this->tagId;
            }
            // bibtexString ID
            if (isset($this->bibtexStringId))
            {
                $tsArray["import_BibtexStringId"] = $this->bibtexStringId;
            }
            // Resource IDs
            $tsArray["import_ResourceIds"] = $this->rIds;
            // Remaining entries
            $tsArray["import_Entries"] = $this->entriesLeft;
            // Rejected titles
            if (!empty($this->rejectTitles))
            {
                $tsArray["import_RejectTitles"] = $this->rejectTitles;
            }
            // Rejected titles
            if (!empty($this->rejects))
            {
                $tsArray["import_Rejects"] = $this->rejects;
            }
            // garbage files
            $tsArray["import_GarbageFiles"] = $this->garbageFiles;
            // Unrecognised field mapping
            if (isset($this->unrecognisedFields))
            {
                $tsArray["import_UnrecognisedFields"] = $this->unrecognisedFields;
                // Custom field mapping
                if (isset($this->customFields))
                {
                    $tsArray["import_CustomFields"] = $this->customFields;
                }
                $tsArray["import_ThisVars"] = $this->vars;
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
            $pString .= \FORM\hidden('method', 'continueImport');
            $pString .= \FORM\hidden('uuid', $uuid);
            $pString .= HTML\p(FORM\formSubmit($this->coremessages->text("submit", "Continue")));
            $pString .= FORM\formEnd();
            $tsArray['heading'] = $this->pluginmessages->text("headerImportEndnote");
            $tsArray['form'] = $pString;
            \TEMPSTORAGE\store($this->db, $uuid, $tsArray);
            header("Location: index.php?action=import_IMPORTCOMMON_CORE&method=importContinue&uuid=$uuid");
            die;
        }
    }
    /**
     * endnoteVersion - Endnote versions earlier than 8.0 have a _very_ different XML format and
     * type naming to version 8.0 *&^$*&^!  Load appropriate mapping arrays
     */
    private function endnoteVersion()
    {
        if ($this->version8)
        {
            $this->importTypes = $this->map->importTypes8;
            $this->endnoteXmlFields = $this->map->endnoteXmlFields8;
        }
        else
        {
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
        if (array_key_exists('import_Quarantine', $this->formData))
        {
            $this->import->quarantine = TRUE;
        }
        if (array_key_exists('import_KeywordIgnore', $this->formData))
        {
            $this->import->kwIgnore = TRUE;
        }
        $tagWritten = FALSE;
        if (!$continue)
        {
            $this->tagId = FALSE;
        }
        $finalInput = TRUE;
        foreach ($this->entries as $key => $entry)
        {
            $custom = [];
            unset($this->entriesLeft[$key]);
            $this->entry = $entry;
            // If type == 'book' or 'book_article', need to swap 'year1' (WIKINDX's original publication year) and
            // 'year2' (WIKINDX's reprint year)
            if ((($this->entry['type'] == 'book') || ($this->entry['type'] == 'book_article')) &&
                array_key_exists('resource_year', $this->entry) &&
                array_key_exists('resourceyearYear1', $this->entry['resource_year'])
                && array_key_exists('resourceyearYear2', $this->entry['resource_year']))
            {
                $year2 = $this->entry['resource_year']['resourceyearYear2'];
                $this->entry['resource_year']['resourceyearYear2'] = $this->entry['resource_year']['resourceyearYear1'];
                $this->entry['resource_year']['resourceyearYear1'] = $year2;
            }
            list($noSort, $title, $subtitle) = $this->common->splitTitle($this->entry['title'], $this->formData);
            if ($this->common->checkDuplicates($noSort, $title, $subtitle, $this->entry['type'], $this->formData)
            ||
            (array_search($this->entry['type'], WIKINDX_DEACTIVATE_RESOURCE_TYPES) !== FALSE))
            {
                $rejectTitle = $this->entry['title'] . ".";
                $this->rejectTitles[] = $rejectTitle;
                $this->resourceDiscarded++;
                $this->resourceDiscardedThisRound++;

                continue;
            }
            $this->publisherId = $this->collectionId = FALSE;
            $this->grabDate($this->entry['type']);
            if (array_key_exists($key, $this->rejects))
            {
                $custom = $this->reject($key);
            }
            $this->resourceId = $this->writeResourceTable($noSort, $title, $subtitle);
            // add any import tag and get tag auto ID.  We write it here after the resource table in case we forbid duplicates and all
            // endnote entries are duplicates - we don't want an empty tag in the WKX_tag table.
            if (!$continue)
            {
                if (!$tagWritten)
                {
                    $this->tagId = $this->common->writeTagTable($this->formData);
                    $tagWritten = TRUE;
                }
            }
            if (array_key_exists('creators', $this->entry))
            {
                $creators = [];
                foreach ($this->entry['creators'] as $creatorRole => $creatorRoleArray)
                {
                    $creatorRoleString = implode(" and ", $creatorRoleArray);
                    $creators[$creatorRole] = $this->parseCreator->parse($creatorRoleString);
                }
                $this->deleteCacheCreators = TRUE;
            }
            else
            {
                $creators = [];
            }
            $this->common->writeCreatorTables($creators);
            $this->writePublisherTable();
            $this->writeCollectionTable();
            $this->writeResourceMiscTable();
            $this->writeResourceYearTable();
            $this->writeResourcePageTable();
            $this->writeResourceKeywordTable();
            if (!empty($custom))
            {
                $this->writeResourceCustomTable($custom);
            }
            $this->common->writeResourceCategoryTable($this->formData["import_Categories"]);
            $this->writeResourceTextTable();
            $this->common->writeResourceTimestampTable();
            $this->common->writeUserbibliographyresourceTable($this->formData["import_BibId"]);
            $this->common->writeBibtexKey();
            $this->resourceAdded++;
            $this->resourceAddedThisRound++;
            // Check we have more than 5 seconds buffer before max_execution_time times out.
            if ((time() - $this->oldTime) >= (ini_get("max_execution_time") - 5))
            {
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
        foreach ($this->rejects[$topKey] as $key => $value)
        {
            $newEntry = [];
            if (($key == 'bibtexEntryType') ||
            ($key == 'howpublished') || ($key == 'abstract') || ($key == 'keywords'))
            {
                $newEntry[$key] = $value;

                continue;
            }
            if ($key == 'note')
            { // Use 'note' in preference to 'annote'
                $newEntry[$key] = $value;

                continue;
            }
            if (($key == 'annote') && !array_key_exists('note', $this->entry))
            { // Use 'note' in preference to 'annote'
                $newEntry[$key] = $value;

                continue;
            }
            if (array_search($key, $this->map->{$wkType}['possible']) !== FALSE)
            {
                if (!array_key_exists($key, $newEntry))
                {
                    $newEntry[$key] = $value;
                }
            }
            // Do we map unrecognised fields?
            if (!empty($this->unrecognisedFields) && array_search($key, $this->unrecognisedFields) !== FALSE)
            {
                $importKey = 'import_' . $key;
                if (array_key_exists($importKey, $this->vars) &&
                    array_search($this->vars[$importKey], $this->map->{$wkType}['possible']) !== FALSE)
                {
                    // Do unrecognised fields take precedence?
                    if (array_key_exists('import_Precedence', $this->vars))
                    {
                        $newEntry[$this->vars[$importKey]] = $value;

                        continue;
                    }
                    if (!array_key_exists($this->vars[$importKey], $newEntry))
                    {
                        $newEntry[$this->vars[$importKey]] = $value;

                        continue;
                    }
                }
            }
            if (array_key_exists($key, $newEntry))
            {
                continue;
            }
            if (!empty($this->customFields) && array_key_exists($key, $this->customFields))
            {
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
        $tmp = \UTF8\mb_explode(" ", microtime());

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
        if ($this->entry['type'] == 'thesis')
        {
            $fields[] = 'resourceField1';
            $values[] = 'thesis';
        }
        $fields[] = 'resourceType';
        $values[] = $this->entry['type'];
        $fields[] = 'resourceTitle';
        $values[] = $title;
        $titleSort = $title;
        if ($noSort)
        {
            $fields[] = 'resourceNoSort';
            $values[] = $noSort;
        }
        if ($subtitle)
        {
            $fields[] = 'resourceSubtitle';
            $values[] = $subtitle;
            $titleSort .= ' ' . $subtitle;
        }
        $fields[] = 'resourceTitleSort';
        $values[] = str_replace(['{', '}'], '', $titleSort);
        if (array_key_exists('resource', $this->entry))
        {
            foreach ($this->entry['resource'] as $field => $value)
            {
                if (($this->entry['type'] == 'thesis') && ($field == 'resourceField1'))
                {
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
        if (!array_key_exists('resource_collection', $this->entry))
        {
            return;
        }
        $title = $short = FALSE;
        if (array_key_exists('collectionTitle', $this->entry['resource_collection']))
        {
            $title = trim($this->entry['resource_collection']['collectionTitle']);
        }
        if (array_key_exists('collectionTitleShort', $this->entry['resource_collection']))
        {
            $short = trim($this->entry['resource_collection']['collectionTitleShort']);
        }
        if (!$title)
        {
            return;
        }
        $this->collectionId = $this->common->writeCollectionTable($title, $short, $this->entry['type']);
        $this->deleteCacheCollections = TRUE;
    }
    /**
     * writePublisherTable - write WKX_publisher table
     */
    private function writePublisherTable()
    {
        if (!array_key_exists('resource_publisher', $this->entry))
        {
            return;
        }
        $publisherName = $publisherLocation = FALSE;
        if (array_key_exists('publisherName', $this->entry['resource_publisher']))
        {
            $publisherName = trim($this->entry['resource_publisher']['publisherName']);
        }
        if (array_key_exists('publisherLocation', $this->entry['resource_publisher']))
        {
            $publisherLocation = trim($this->entry['resource_publisher']['publisherLocation']);
        }
        if (!$publisherName)
        {
            return;
        }
        $this->publisherId = $this->common->writePublisherTable($publisherName, $publisherLocation, $this->entry['type']);
        $this->deleteCachePublishers = TRUE;
    }
    /**
     * writeResourceMiscTable - write WKX_resource_misc table
     */
    private function writeResourceMiscTable()
    {
        $intRequired = ['resourcemiscField1', 'resourcemiscField2', 'resourcemiscField3', 'resourcemiscField4',
            'resourcemiscField5', 'resourcemiscField6', ];
        if (array_key_exists('resource_misc', $this->entry))
        {
            foreach ($this->entry['resource_misc'] as $field => $value)
            {
                $fields[] = $field;
                if (in_array($field, $intRequired))
                {
                    $values[] = preg_replace("/[^0-9]/", '', $value);
                }
                else
                {
                    $values[] = $value;
                }
            }
        }
        if ($this->collectionId)
        {
            $fields[] = 'resourcemiscCollection';
            $values[] = $this->collectionId;
        }
        if ($this->publisherId)
        {
            $fields[] = 'resourcemiscPublisher';
            $values[] = $this->publisherId;
        }
        if ($this->tagId)
        {
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
        if (array_key_exists('resource_year', $this->entry))
        {
            foreach ($this->entry['resource_year'] as $field => $value)
            {
                $fields[] = $field;
                $values[] = $value;
            }
        }
        if (!isset($fields))
        {
            return;
        }
        $this->common->writeYearTable($fields, $values);
    }
    /**
     * writeResourcePageTable - write WKX_resource_page table
     */
    private function writeResourcePageTable()
    {
        if (!array_key_exists('PageStart', $this->entry))
        {
            return;
        }
        $fields[] = 'resourcepagePageStart';
        $values[] = $this->entry['PageStart'];
        if (array_key_exists('PageEnd', $this->entry))
        {
            $fields[] = 'resourcepagePageEnd';
            $values[] = $this->entry['PageEnd'];
        }
        if (!isset($fields))
        {
            return;
        }
        $this->common->writePageTable($fields, $values);
    }
    /**
     * writeResourceTextTable - write WKX_resource_text table
     */
    private function writeResourceTextTable()
    {
        $notes = $abstract = $url = FALSE;
        if (array_key_exists('notes', $this->entry))
        {
            $notes = $this->entry['notes'];
        }
        if (array_key_exists('abstract', $this->entry))
        {
            $abstract = $this->entry['abstract'];
        }
        if (array_key_exists('URLS', $this->entry))
        {
            $url = $this->entry['URLS'];
        }
        $this->common->writeResourcetextTable($notes, $abstract, $url);
    }
    /**
     * writeResourceKeywordTable - write WKX_resource_keyword table
     */
    private function writeResourceKeywordTable()
    {
        if (array_key_exists('keywords', $this->entry))
        {
            $this->common->writeKeywordTables($this->entry['keywords']);
            $this->deleteCacheKeywords = TRUE;
        }
    }
    /**
     * writeResourceCustomTable - write WKX_resource_custom table
     *
     * @param mixed $custom assoc array of one entry for import
     */
    private function writeResourceCustomTable($custom)
    {
        if (empty($this->customFields))
        {
            return;
        }
        foreach ($this->customFields as $importKey => $id)
        {
            if (!array_key_exists($importKey, $custom))
            {
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
        if (array_key_exists('date', $this->entry))
        {
            if (!array_key_exists('resource_misc', $this->map->{$type}) or empty($this->map->{$type}['resource_misc']))
            {
                return;
            }
            list($month, $day, $year) = $this->date->init($this->entry['date']);
            if (!$year && array_key_exists('year', $this->entry))
            { // February 31 or 31 February and no year
                $year = $this->entry['year'];
            }
            if ($month && ($key = array_search('Month', $this->map->{$type}['resource_misc'])))
            {
                $this->entry['resource_misc'][$key] = $month;
            }
            if ($day && ($key = array_search('Day', $this->map->{$type}['resource_misc'])))
            {
                $this->entry['resource_misc'][$key] = $day;
            }
            if ($year && array_key_exists('resource_year', $this->map->{$type}) &&
                array_key_exists('resourceyearYear1', $this->map->{$type}['resource_year']))
            {
                $this->entry['resource_year']['resourceyearYear1'] = $year;
            }
            if (!$day && !$month && !$year)
            {
                $this->reject['Date'] = $this->entry['date'];
            }
            unset($this->entry['date']);
        }
    }
    /**
     * gatherStage1 - gather input from stage 1 and return a fullpath filename for parsing.
     *
     * If $this->type == 'paste', this is a user cut 'n' pasting bibtex entries in a textarea box. We write the input to a
     * temporary file.
     *
     * @return string
     */
    private function gatherStage1()
    {
        $error = '';
        // a multiple select box so handle as array
        if (array_key_exists('import_Categories', $this->vars) && $this->vars['import_Categories'])
        {
            $this->formData["import_Categories"] = $this->vars['import_Categories'];
        }
        else
        { // force to 'General'
            $this->formData["import_Categories"] = [1];
        }
        // a multiple select box so handle as array
        if (array_key_exists('import_BibId', $this->vars) && $this->vars['import_BibId'])
        {
            $this->formData["import_BibId"] = $this->vars['import_BibId'];
        }
        if (array_key_exists('import_KeywordIgnore', $this->vars))
        {
            $this->formData["import_KeywordIgnore"] = $this->vars['import_KeywordIgnore'];
        }
        if (array_key_exists('import_ImportDuplicates', $this->vars))
        {
            $this->formData["import_ImportDuplicates"] = $this->vars['import_ImportDuplicates'];
        }
        if (array_key_exists('import_Quarantine', $this->vars))
        {
            $this->formData["import_Quarantine"] = $this->vars['import_Quarantine'];
        }
        $this->formData["import_TitleSubtitleSeparator"] = $this->vars['import_TitleSubtitleSeparator'];
        if (!array_key_exists("import_UnrecognisedFields", $this->formData))
        {
            if (!isset($_FILES['import_File']))
            {
                if ($file = $this->session->getVar("import_File"))
                {
                    return implode(DIRECTORY_SEPARATOR, [$this->dirName, $file]);
                }
                else
                {
                    $error = $this->errors->text("file", "upload");
                }
            }
            // Check for file input
            $fileName = \UTILS\uuid();
            if (!move_uploaded_file($_FILES['import_File']['tmp_name'], implode(DIRECTORY_SEPARATOR, [$this->dirName, $fileName])))
            {
                $error = $this->errors->text("file", "upload");
            }
            $this->formData['import_File'] = $fileName;
            if ($this->vars['import_Tag'])
            {
                if ($tagId = $this->tag->checkExists(\UTF8\mb_trim($this->vars['import_Tag'])))
                { // Existing tag found
                    $this->formData['import_TagId'] = $tagId;
                }
                else
                {
                    $this->formData['import_Tag'] = \UTF8\mb_trim($this->vars['import_Tag']);
                }
            }
            elseif (array_key_exists('import_TagId', $this->vars) && $this->vars['import_TagId'])
            {
                $this->formData['import_TagId'] = $this->vars['import_TagId'];
            }
            $this->garbageFiles[implode(DIRECTORY_SEPARATOR, [$this->dirName, $fileName])] = FALSE;
            if ($error)
            {
                $this->badInput($error);
            }

            return implode(DIRECTORY_SEPARATOR, [$this->dirName, $fileName]);
        }
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
        if ($entry['ref-type-name'] == 'Edited Book')
        {
            $type = $this->entry['type'] = 'book';
        }
        else
        {
            $type = $this->entry['type'] = array_search($entry['ref-type-name'], $this->importTypes);
        }
        unset($entry['ref-type']);
        unset($entry['ref-type-name']);
        $this->accessYear = $this->accessDate = FALSE;
        foreach ($entry as $key => $value)
        {
            $this->extractEntries($key, $value, $type);
        }
        if ($this->accessYear && $this->accessDate)
        {
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
        if (is_array($value))
        {
            $value = $this->extractFromStyleArray($value);
        }
        $mapped = $pages = $volume = $number = FALSE;
        if ($key == 'pages')
        {
            $pages = $value;
        }
        if ($key == 'PAGES')
        {
            $pages = $value;
        }
        if ($pages && array_key_exists('resource_page', $this->map->{$type}))
        {
            list($this->entry['PageStart'], $this->entry['PageEnd']) = $this->pages->init($pages);

            return;
        }
        // Endnote stores the last update date in pub-dates and access year in volume and access date in number for its
        // Electronic Source.  We want the last two if $type == 'web_article or 'database' and don't want 'pub-dates'.
        if ($key == 'volume')
        {
            $volume = $value;
        }
        elseif ($key == 'VOLUME')
        {
            $volume = $value;
        }
        if ($key == 'number')
        {
            $number = $value;
        }
        elseif ($key == 'NUMBER')
        {
            $number = $value;
        }
        if ($volume && (($type == 'web_article') || ($type == 'database') ||
            ($type == 'web_encyclopedia') || ($type == 'web_encyclopedia_article') || ($type == 'web_site')))
        {
            $this->accessYear = trim($volume);

            return;
        }
        if ($number && (($type == 'web_article') || ($type == 'database') ||
            ($type == 'web_encyclopedia') || ($type == 'web_encyclopedia_article') || ($type == 'web_site')))
        {
            $this->accessDate = trim($number);

            return;
        }
        foreach ($this->map->$type as $mapTable => $mapArray)
        {
            if (array_key_exists($key, $this->endnoteXmlFields) &&
                ($mapKey = array_search($this->endnoteXmlFields[$key], $mapArray)))
            {
                $mapped = TRUE;
                $this->entry[$mapTable][$mapKey] = $value;

                break;
            }
        }
        if (($key == 'abstract') || ($key == 'ABSTRACT') ||
            ($key == 'notes') || ($key == 'NOTES'))
        {
            $mapped = TRUE;
            $this->entry[$key] = $value;
        }
        if (!$mapped)
        {
            $this->reject[$key] = $value;
        }
        if ($mapped)
        {
            return;
        }
        if ($key == 'contributors')
        {
            $this->extractContributors($value, $type);
        }
        elseif ($key == 'urls')
        {
            $this->extractUrl($value, $type);
        }
        elseif (($key == 'keywords') || ($key == 'KEYWORDS'))
        {
            $this->extractKeywords($value, $type);
        }
        elseif (is_array($value))
        {
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
        if (is_array($value) && @array_shift(array_keys($value)) == 'style')
        {
            return $value['style'][0];
        }
        else
        {
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
        foreach ($array as $key => $value)
        {
            if ($key == 'title')
            {
                $this->entry['title'] = $this->extractFromStyleArray($value[0]);

                continue;
            }
            // Endnote stores a patent's international author in the tertiary-title field and the international title
            // in the tertiary-author field.  Why I ask you?
            if (($type == 'patent') && ($key == 'tertiary-title') && ($mapKey = array_search(
                $this->endnoteXmlFields[$key],
                $this->map->{$type}['resource_creator']
            )))
            {
                $this->entry[$mapKey][] = $this->extractFromStyleArray($value[0]);

                continue;
            }
            $mapped = FALSE;
            foreach ($this->map->$type as $mapTable => $mapArray)
            {
                if (array_key_exists($key, $this->endnoteXmlFields) &&
                    ($mapKey = array_search($this->endnoteXmlFields[$key], $mapArray)))
                {
                    $mapped = TRUE;
                    $this->entry[$mapTable][$mapKey] = $this->extractFromStyleArray($value[0]);

                    break;
                }
            }
            if (!$mapped)
            {
                $this->reject[$key] = $this->extractFromStyleArray($value[0]);
            }
            // Endnote stores the last update date in pub-dates and access year in volume and access date in number for its
            // Electronic Source.  We want the last two if $type == 'web_article or 'database' and don't want 'pub-dates'.
            if ($key == 'pub-dates')
            {
                if (($type != 'web_article') && ($type != 'database') &&
                    ($type != 'web_site') && ($type != 'web_encyclopedia') && ($type != 'web_encyclopedia_article'))
                {
                    foreach ($value[0] as $dateKey => $dateValue)
                    {
                        if ($dateKey == 'date')
                        {
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
        foreach ($array['keyword'] as $value)
        {
            $keyword = $this->extractFromStyleArray($value);
            if (!array_key_exists('keywords', $this->entry) || (array_search($keyword, $this->entry['keywords']) === FALSE))
            {
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
        foreach ($array as $aKey => $value)
        {
            foreach ($value as $aValue)
            {
                foreach ($aValue as $authorKey => $authorValue)
                {
                    if (array_key_exists($aKey, $this->endnoteXmlFields) &&
                        ($mapKey = array_search($this->endnoteXmlFields[$aKey], $this->map->{$type}['resource_creator'])))
                    {
                        foreach ($authorValue as $authorElement)
                        {
                            $this->entry['creators'][$mapKey][] = $this->extractFromStyleArray($authorElement);
                        }

                        continue;
                    }
                    // Endnote stores a patent's international author in the tertiary-title field and the international title
                    // in the tertiary-author field.  Why I ask you?
                    if (($type == 'patent') &&
                        ($mapKey = array_search($this->endnoteXmlFields[$authorKey], $this->map->{$type}['resource'])) &&
                        ($authorKey == 'tertiary-authors'))
                    {
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
        if (!is_array($array))
        {
            return;
        }
        foreach ($array as $key => $value)
        {
            if ($key == 'related-urls')
            {
                $uArray = [];
                foreach ($value[0] as $urlKey => $urlValue)
                {
                    if (($urlKey == 'url') && array_key_exists($urlKey, $this->endnoteXmlFields) &&
                        (FALSE !== array_search($this->endnoteXmlFields[$urlKey], $this->map->{$type}['resource_url'])))
                    {
                        $uArray[] = $this->extractFromStyleArray($urlValue[0]);
                    }
                }
                if (!empty($uArray))
                {
                    $this->entry['URLS'] = $uArray;
                }
                else
                {
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
        $this->badInput->close($this->errors->text("inputError", "invalid"), $this->common, ['display', $this->formData]);
//        $this->parentClass->initEndnoteImport($error);
//        FACTORY_CLOSE::getInstance();
    }
}
