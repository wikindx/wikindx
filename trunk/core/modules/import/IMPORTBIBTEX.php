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
 *	IMPORTBIBTEX: BibTeX import class
 */
class IMPORTBIBTEX
{
    public $importFile = FALSE;
    public $type = FALSE;
    private $db;
    private $vars;
    private $gatekeep;
    private $errors;
    private $success;
    private $messages;
    private $session;
    private $import;
    private $badInput;
    private $bibConfig;
    private $resourceAdded = 0;
    private $resourceDiscarded = 0;
    private $resourceAddedThisRound = 0;
    private $resourceDiscardedThisRound = 0;
    private $editionNumbers;
    private $map;
    private $tag;
    private $parseCreator;
    private $monthObj;
    private $pages;
    private $parse;
    private $oldTime;
    /** array */
    private $rejectTitles = [];
    /** array */
    private $rejected = [];
    private $fileName = FALSE;
    private $fileNameStrings = FALSE;
    /** array */
    private $entries = [];
    /** array */
    private $strings = [];
    private $entriesLeft;
    private $tagId;
    private $keywords;
    private $note;
    private $abstract;
    private $url;
    private $month;
    private $day;
    private $thesisType = FALSE;
    private $howPublished;
    private $publisherId = FALSE;
    private $confPublisherId = FALSE;
    private $collectionId = FALSE;
    private $bibtexStringId = FALSE;
    private $customFields;
    private $unrecognisedFields;
    /** array */
    private $invalidFieldNames = [];
    private $startMonth;
    private $startDay;
    private $endMonth;
    private $endDay;
    private $resourceId;
    private $deleteCacheCreators = FALSE;
    private $deleteCachePublishers = FALSE;
    private $deleteCacheCollections = FALSE;
    private $deleteCacheKeywords = FALSE;
    private $badClass;
    private $badFunction;
    private $dirName;
    /** array */
    private $garbageFiles = [];
    private $errorMessage;
    /** array */
    private $rIds = [];
    /** array */
    private $formData = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "IMPORTCOMMON.php"]));
        $this->import = new IMPORTCOMMON();
        $this->import->importType = 'bibtex';
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->bibConfig = FACTORY_BIBTEXCONFIG::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->map = FACTORY_BIBTEXMAP::getInstance();
        // need to use English constants for BibTeX
        $constants = FACTORY_CONSTANTS::getFreshInstance(TRUE);
        $this->tag = FACTORY_TAG::getInstance();
        $this->parseCreator = FACTORY_BIBTEXCREATORPARSE::getInstance();
        $this->monthObj = FACTORY_BIBTEXMONTHPARSE::getInstance();
        $this->pages = FACTORY_BIBTEXPAGEPARSE::getInstance();
        $this->parse = FACTORY_BIBTEXPARSE::getInstance();
        $constants->convertNumbers();
        $this->editionNumbers = array_flip($constants->cardinalToOrdinalWord());
        $this->parseCreator->separateInitials = TRUE;
        // Load bibConfig arrays
        $this->bibConfig->bibtex();
        $this->oldTime = time();
        $this->dirName = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES]);
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'paste'))
        {
            $this->type = 'paste';
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "PASTEBIBTEX.php"]));
            $this->badClass = new PASTEBIBTEX();
            $this->badFunction = 'init';
        }
        elseif (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'file'))
        {
            $this->type = 'file';
            include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "BIBTEXFILE.php"]));
            $this->badClass = new BIBTEXFILE();
            $this->badFunction = 'init';
        }
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "bibtexImport"));
    }
    /**
     * stage1 - start the process
     */
    public function stage1()
    {
        if (!$this->importFile)
        {
            $this->gatekeep->init();
        }
        $this->fileName = $this->gatherStage1();
        $this->parse->expandMacro = TRUE; // substitute @string values
        $this->parse->openBib($this->fileName);
        $this->parse->extractEntries();
        $this->parse->closeBib();
        list($null, $this->strings, $entries) = $this->parse->returnArrays(); // don't need preamble
        $this->findInvalidFields($entries);
        // NB - we need to write data to database as UTF-8 and parse all bibTeX values for laTeX code
        $this->entriesLeft = $this->entries = $this->convertEntries($entries);
        $finalInput = $this->writeDb();
        $this->import->collectionDefaults();
        $this->cleanUp($finalInput);
    }
    /**
     * stage2Invalid - following on from invalid fields having been found
     */
    public function stage2Invalid()
    {
        $this->gatekeep->init();
        $this->formData = \TEMPSTORAGE\fetch($this->db, $this->vars['uuid']);
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
                $this->entries = $this->convertEntries(unserialize(base64_decode(trim(fgets($this->fileName)))));
            }
            fclose($this->fileName);
        }
        if (array_key_exists("import_FileNameStrings", $this->formData))
        {
            if (!is_file($this->formData["import_FileNameStrings"]))
            {
                $this->badInput->close(
                    $this->errors->text("file", "read", $this->formData["import_FileNameStrings"]),
                    $this->badClass,
                    $this->badFunction
                );
            }
            $this->garbageFiles[$this->formData["import_FileNameStrings"]] = FALSE;
            if ($this->fileNameStrings = fopen($this->formData["import_FileNameStrings"], 'r'))
            {
                if (!feof($this->fileNameStrings))
                {
                    $this->strings = $this->convertEntries(unserialize(base64_decode(trim(fgets($this->fileNameStrings)))));
                }

                fclose($this->fileNameStrings);
            }
        }
        list($error, $this->customFields, $this->unrecognisedFields) = $this->import->getUnrecognisedFields($this->formData);
        if ($error)
        {
            $this->badInput->close($error, $this->badClass, $this->badFunction);
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
     * getType - figure out what wikindx type this entry is
     *
     * @param array $entry Assoc array of one entry for import.
     *
     * @return string The WIKINDX resource type for this bibtex entry
     */
    public function getType($entry)
    {
        if ($entry['bibtexEntryType'] == 'article')
        {
            if ($this->day)
            {
                $wkType = 'newspaper_article';
            }
            elseif ($this->month)
            {
                $wkType = 'magazine_article';
            }
            else
            { // no day or month
                $wkType = 'journal_article';
            }
        }
        elseif (($entry['bibtexEntryType'] == 'misc') && $this->url)
        {
            $wkType = 'web_article';
        }
        elseif ($entry['bibtexEntryType'] == 'misc')
        {
            $wkType = 'miscellaneous';
        }
        elseif ($entry['bibtexEntryType'] == 'book')
        {
            $wkType = 'book';
        }
        elseif ($entry['bibtexEntryType'] == 'techreport')
        {
            $wkType = 'report';
        }
        elseif ($entry['bibtexEntryType'] == 'patent')
        {
            $wkType = 'patent';
        }
        elseif ($entry['bibtexEntryType'] == 'unpublished')
        {
            $wkType = 'unpublished';
        }
        elseif ($entry['bibtexEntryType'] == 'mastersthesis')
        {
            $wkType = 'thesis';
            $this->thesisType = "masters";
        }
        elseif ($entry['bibtexEntryType'] == 'phdthesis')
        {
            $wkType = 'thesis';
            $this->thesisType = "PhD";
        }
        elseif (($entry['bibtexEntryType'] == 'conference') ||
            ($entry['bibtexEntryType'] == 'inproceedings'))
        {
            $wkType = 'proceedings_article';
        }
        // inbook type with a chapter field that is numeric, bibtex field 'chapter' is wikindx's title, bibtex field 'title' is wikindx collectionTitle
        elseif (($entry['bibtexEntryType'] == 'inbook') && array_key_exists('chapter', $entry) &&
            is_numeric($entry['chapter']))
        {
            $wkType = 'book_chapter';
        }
        // incorrect bibtex but we allow it anyhow making it a wikindx book_article type
        elseif (($entry['bibtexEntryType'] == 'inbook') && array_key_exists('chapter', $entry))
        {
            $wkType = 'book_article';
        }
        elseif (($entry['bibtexEntryType'] == 'incollection') ||
            ($entry['bibtexEntryType'] == 'inbook'))
        {
            $wkType = 'book_article';
        }
        elseif (($entry['bibtexEntryType'] == 'collection') ||
            ($entry['bibtexEntryType'] == 'proceedings'))
        {
            $wkType = 'proceedings';
        }
        elseif (!$wkType = array_search($entry['bibtexEntryType'], $this->map->types))
        {
            $wkType = 'miscellaneous'; // everything else
        }

        return $wkType;
    }
    /**
     * writeResourceMiscTable - write WKX_resource_misc table
     *
     * @param array $entry Assoc array of one entry for import.
     * @param string $wkType The WIKINDX resource type for this bibtex entry
     */
    public function writeResourceMiscTable($entry, $wkType)
    {
        $intRequired = ['resourcemiscField1', 'resourcemiscField2', 'resourcemiscField3', 'resourcemiscField4',
            'resourcemiscField5', 'resourcemiscField6', ];
        foreach ($entry as $bibField => $bibValue)
        {
            if ($wkField = array_search($bibField, $this->map->{$wkType}['resource_misc']))
            {
                $fields[] = $wkField;
                if (in_array($wkField, $intRequired))
                {
                    $values[] = preg_replace("/[^0-9]/", '', $bibValue);
                }
                else
                {
                    $values[] = $bibValue;
                }
            }
        }
        if ($this->collectionId)
        {
            $fields[] = 'resourcemiscCollection';
            $values[] = $this->collectionId;
            $this->collectionId = FALSE;
        }
        if ($this->publisherId)
        {
            $fields[] = 'resourcemiscPublisher';
            $values[] = $this->publisherId;
            $this->publisherId = FALSE;
        }
        if ($this->confPublisherId)
        {
            $fields[] = "resourcemiscField1";
            $values[] = $this->confPublisherId;
            $this->confPublisherId = FALSE;
        }
        if ($this->tagId)
        {
            $fields[] = 'resourcemiscTag';
            $values[] = $this->tagId;
        }
        if (($wkType == 'newspaper_article') || ($wkType == 'magazine_article') ||
            ($wkType == 'proceedings_article') || ($wkType == 'proceedings') ||
            ($wkType == 'journal_article') || ($wkType == 'report') ||
            ($this->url && ($wkType == 'web_article')))
        {
            if ($this->startMonth)
            {
                $fields[] = 'resourcemiscField3';
                $values[] = $this->startMonth;
            }
            if ($this->startDay)
            {
                $fields[] = 'resourcemiscField2';
                $values[] = $this->startDay;
            }
        }
        if (($wkType == 'proceedings_article') || ($wkType == 'proceedings') || ($wkType == 'magazine_article'))
        {
            if ($this->endMonth)
            {
                $fields[] = 'resourcemiscField6';
                $values[] = $this->endMonth;
            }
            if ($this->endDay)
            {
                $fields[] = 'resourcemiscField5';
                $values[] = $this->endDay;
            }
        }
        $fields[] = 'resourcemiscAddUserIdResource';
        $values[] = $this->session->getVar("setup_UserId");
        $this->import->writeResourcemiscTable($fields, $values);
    }
    /**
     * writeResourceCustomTable - write WKX_resource_custom table
     *
     * @param mixed $custom
     */
    public function writeResourceCustomTable($custom)
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
            $this->import->writeResourcecustomTable($custom[$importKey], $id);
        }
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
            \TEMPSTORAGE\store(
                $this->db,
                $uuid,
                ['rIds' => $this->rIds, 'resourceAdded' => $this->resourceAdded, 'garbageFiles' => $this->garbageFiles,
                    'resourceDiscarded' => $this->resourceDiscarded, 'rejectTitles' => $this->rejectTitles,
                    'deleteCacheCreators' => $this->deleteCacheCreators, 'deleteCachePublishers' => $this->deleteCachePublishers,
                    'deleteCacheCollections' => $this->deleteCacheCollections, 'deleteCacheKeywords' => $this->deleteCacheKeywords, ]
            );
            header("Location: index.php?action=import_IMPORTCOMMON_CORE&method=importSuccess&success=bibtexImport&uuid=$uuid");
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
            $pString = \HTML\p($this->messages->text(
                "import",
                "executionTimeExceeded",
                ini_get("max_execution_time")
            ));
            $pString .= \HTML\p($this->messages->text("import", "addedChunk", " " .
                $this->resourceAddedThisRound));
            $pString .= \HTML\p("$remainder entries remaining.");
            $pString .= \FORM\formHeader("import_IMPORTBIBTEX_CORE");
            $pString .= \FORM\hidden('method', 'continueImport');
            $pString .= \FORM\hidden('uuid', $uuid);
            $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Continue")));
            $pString .= \FORM\formEnd();
            $tsArray['heading'] = $this->messages->text("heading", "bibtexImport");
            $tsArray['form'] = $pString;
            \TEMPSTORAGE\store($this->db, $uuid, $tsArray);
            header("Location: index.php?action=import_IMPORTCOMMON_CORE&method=importContinue&uuid=$uuid");
            die;
        }
    }
    /**
     * find unrecognised field names
     *
     * @param mixed $entries
     *
     * @return string
     */
    private function findInvalidFields($entries)
    {
        $inputTypes = [];
        $this->day = $this->month = FALSE;
        foreach ($entries as $entry)
        {
            list($this->url) = $this->grabHowPublished($entry);
            $this->getType($entry);
            foreach ($entry as $field => $value)
            {
                if ($field == 'bibtexEntryType')
                {
                    $inputTypes[] = $value;

                    continue;
                }
                if ($field == 'bibtexCitation')
                {
                    continue;
                }
                if (($field == 'annote') && !array_key_exists('note', $entry))
                {
                    continue;
                }
                if ((array_search($field, $this->map->validFields) === FALSE) &&
                    (array_search($field, $this->invalidFieldNames) === FALSE))
                {
                    $this->invalidFieldNames[] = $field;
                }
            }
        }
        if (!empty($this->invalidFieldNames))
        { // prompt to map field names
            list($error, $pString, $uuid) = $this->import->promptFieldNames(
                $entries,
                $inputTypes,
                $this->map,
                $this->invalidFieldNames,
                $this->strings,
                $this->formData
            );
            if ($error)
            {
                $this->badInput->close($error, $this->badClass, $this->badFunction);
            }
            else
            {
                @unlink($this->fileName); // remove garbage - ignore errors
                \TEMPSTORAGE\store($this->db, $uuid, ['form' => $pString, 'heading' => $this->messages->text("heading", "bibtexImport")]);
                header("Location: index.php?action=import_IMPORTCOMMON_CORE&method=importInvalidFields&uuid=$uuid");
                die;
            }
        }

        return FALSE; // continue with import.
    }
    /**
     * reject -- gather rejected fields that wikindx does not recognise for that type and remove from $entry
     *
     * @param array $entry - assoc array of one entry for import.
     * @param string $wkType - the WIKINDX resource type for this bibtex entry
     *
     * @return array [$rejected, $newEntry, $custom]
     *
     * $rejected - array of rejected field and their values (with bibTeX delimiters added back in)
     * $newEntry - $entry with $rejected elements removed
     * $custom - ...
     */
    private function reject($entry, $wkType)
    {
        $rejectedEntry = FALSE;
        $custom = [];
        foreach ($entry as $key => $value)
        {
            if (($key == 'bibtexEntryType') ||
            ($key == 'howpublished') || ($key == 'abstract') || ($key == 'keywords'))
            {
                $newEntry[$key] = $value;

                continue;
            }
            if ($key == 'bibtexCitation')
            {
                $rejected['citation'] = trim($value);
                $rejectedEntry = TRUE;

                continue;
            }
            if ($key == 'note')
            { // Use 'note' in preference to 'annote'
                $newEntry[$key] = $value;

                continue;
            }
            if (($key == 'annote') && !array_key_exists('note', $entry))
            { // Use 'note' in preference to 'annote'
                $newEntry[$key] = $value;

                continue;
            }
            if (($key == 'month') && $this->url && ($wkType == 'web_article'))
            {
                continue;
            }
            if (array_search($key, $this->map->{$wkType}['possible']) !== FALSE)
            {
                if (!array_key_exists($key, $newEntry) || !array_key_exists('import_Precedence', $this->vars))
                {
                    $newEntry[$key] = $value;
                }
            }
            // Do we map unrecognised fields?
            if (is_array($this->unrecognisedFields) && !empty($this->unrecognisedFields)
                && array_search($key, $this->unrecognisedFields) !== FALSE)
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
            // If we get here, we have a bibtex field and value that are not recognised by wikindx. If this is not to be mapped, we
            // need to store this in case user has requested that unused fields are also stored in the database.
            // Return any @STRING substitution in $value back to original state
            $rejectedEntry = TRUE;
            // Do @string substitutions
            if (!empty($this->strings) && ($strKey = array_search($value, $this->strings)))
            {
                $rejected[$key] = $strKey;
            }
            // No substitution so return quoted
            else
            {
                $rejected[$key] = "\"" . $value . "\"";
            }
        }
        if (!$rejectedEntry)
        {
            return [FALSE, $newEntry, $custom];
        }

        return [$rejected, $newEntry, $custom];
    }
    /**
     * writeDb - write input to the database.
     *
     * @param bool $continue
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
        $tagWritten = $stringWritten = FALSE;
        if (!$continue)
        {
            $this->tagId = FALSE;
        }
        if ($this->session->getVar("setup_Superadmin") || WIKINDX_IMPORT_BIB)
        {
            $pasteLimit = FALSE;
        }
        else
        {
            $pasteLimit = TRUE;
        }
        $finalInput = TRUE;
        $deactivatedTypes = WIKINDX_DEACTIVATE_RESOURCE_TYPES;
        foreach ($this->entries as $key => $entry)
        {
            unset($this->entriesLeft[$key]);
            $authors = $editors = [];
            // For a user cut 'n' pasting. Admin is unlimited.
            if ($pasteLimit && ($this->resourceAdded >= WIKINDX_MAX_PASTE))
            {
                break;
            }
            $this->keywords = $this->note = $this->abstract = $this->url = $this->month = $this->day = FALSE;
            $wkType = $this->getType($entry);
            $noSort = $title = $subtitle = FALSE;
            // inbook type with a chapter field that is numeric, bibtex field 'chapter' is wikindx's title, bibtex field 'title' is wikindx collectionTitle
            if (($wkType == 'book_chapter') && trim($entry['chapter']))
            {
                $title = trim($entry['chapter']);
            }
            // This was originally bibtex @inbook, because the bibtex field chapter was nonnumeric, it's been converted to wikindx book_article.
            // bibtex field 'chapter' is wikindx's title, bibtex field 'title' is wikindx collectionTitle
            elseif (($wkType == 'book_article') && array_key_exists('chapter', $entry) && trim($entry['chapter']))
            {
                list($noSort, $title, $subtitle) = $this->import->splitTitle($entry['chapter'], $this->formData);
            }
            elseif (array_key_exists('title', $entry) && trim($entry['title']))
            {
                list($noSort, $title, $subtitle) = $this->import->splitTitle($entry['title'], $this->formData);
            }
            // ignore wikindx resource type book_chapter when checking duplicates. Ignore also deactivated types
            if ((!$title || (($wkType != 'book_chapter') && $this->import->checkDuplicates($noSort, $title, $subtitle, $wkType, $this->formData)))
                ||
                (array_search($wkType, $deactivatedTypes) !== FALSE))
            {
                $rejectTitle = $title ? $title . "." : $title;
                if (array_key_exists('author', $entry) && $entry['author'])
                {
                    $rejectTitle = $entry['bibtexEntryType'] . ': ' . trim($entry['author']) . " " . $rejectTitle;
                }
                $this->rejectTitles[] = $rejectTitle;
                $this->resourceDiscarded++;
                $this->resourceDiscardedThisRound++;

                continue;
            }
            if ((array_search('author', $this->map->{$wkType}['resource_creator'])) &&
                array_key_exists('author', $entry) && $entry['author'])
            {
                $authors = $this->parseCreator->parse($entry['author']);
            }
            if ((array_search('editor', $this->map->{$wkType}['resource_creator'])) &&
                array_key_exists('editor', $entry) && $entry['editor'])
            {
                $editors = $this->parseCreator->parse($entry['editor']);
            }
            // bibTeX's 'article' type can be wikindx's journal_article, magazine_article or newspaper_article.  If there is no 'month' field,
            // we assume the first, if there's 'month' but no day part of that field, we assume the second and, if there's a day part, assume
            // the third. So, before we can write the resource table and its `type` field, we need to query any month field in the import.
            list($this->startMonth, $this->startDay, $this->endMonth, $this->endDay) = $this->grabMonth($entry);
            // A bibtex type with a howpublished field containing a URL is mapped to wikindx's web_article type.
            list($this->url, $this->howPublished) = $this->grabHowPublished($entry);
            list($this->rejected, $entry, $custom) = $this->reject($entry, $wkType);
            $this->resourceId = $this->writeResourceTable($noSort, $title, $subtitle, $entry, $wkType);
            $this->rIds[] = $this->resourceId;
            // add any import tag and get tag auto ID.  We write it here after the resource table in case we forbid duplicates and all
            // bibtex entries are duplicates - we don't want an empty tag in the WKX_tag table.  tag auto ID is written to resource_misc
            if (!$continue)
            {
                if (!$tagWritten)
                {
                    $this->tagId = $this->import->writeTagTable($this->formData);
                    $tagWritten = TRUE;
                }
                if (!$stringWritten)
                {
                    $this->writeBibtexStringTable();
                    $stringWritten = TRUE;
                }
            }
            $this->writeCreatorTable($authors, $editors, $wkType);
            $this->writePublisherTable($entry, $wkType);
            $this->writeCollectionTable($entry, $wkType);
            $this->writeResourceMiscTable($entry, $wkType);
            $this->writeResourceYearTable($entry, $wkType);
            $this->writeResourcePageTable($entry, $wkType);
            $this->writeResourcetextTable($entry);
            $this->writeResourceKeywordTable($entry);
            $this->writeResourceCustomTable($custom);
            $this->import->writeResourcecategoryTable($this->formData["import_Categories"]);
            $this->import->writeResourceTimestampTable();
            $this->import->writeImportrawTable($this->rejected, $this->bibtexStringId, $this->formData);
            $this->import->writeUserbibliographyresourceTable($this->formData["import_BibId"]);
            $this->import->writeBibtexKey();
            $this->resourceAdded++;
            $this->resourceAddedThisRound++;
            // Check we have more than 4 seconds buffer before max_execution_time times out.
            if ((time() - $this->oldTime) >= (ini_get("max_execution_time") - 4))
            {
                $finalInput = FALSE;

                break;
            }
        }

        return $finalInput;
    }
    private function writeResourceTable($noSort, $title, $subtitle, $entry, $wkType)
    {
        // bibTeX has no way of saying whether a thesis is a thesis or a dissertation so here we force it to 'thesis'.
        if ($wkType == 'thesis')
        {
            $fields[] = 'resourceField1';
            $values[] = 'thesis';
        }
        $fields[] = 'resourceType';
        $values[] = $wkType;
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
        if ($this->thesisType)
        {
            $fields[] = 'resourceField2';
            $values[] = $this->thesisType;
            $this->thesisType = FALSE;
        }
        if (($wkType == 'miscellaneous') && $this->howPublished)
        {
            $wkField = $this->map->miscellaneous['howpublished'];
            if (array_search($wkField, $fields) === FALSE)
            {
                $fields[] = $wkField;
                $values[] = $this->howPublished;
            }
        }
        foreach ($entry as $bibField => $bibValue)
        {
            // ISBN, ISSN and URL are uppercase in BIBTEXMAP but everything in $entry is lowercase
            if (($bibField == 'isbn') || ($bibField == 'issn') || ($bibField == 'doi'))
            {
                $bibField = mb_strtoupper($bibField);
            }
            if ($wkField = array_search($bibField, $this->map->{$wkType}['resource']))
            {
                if (array_search($wkField, $fields) === FALSE)
                {
                    $fields[] = $wkField;
                    $values[] = $bibValue;
                }
            }
        }

        return $this->import->writeResourceTable($fields, $values);
    }
    /**
     * writeResourceTable - write WKX_resource table and get lastAutoId
     *
     * @param mixed $noSort
     * @param string $title
     * @param string $subtitle
     * @param array $entry - assoc array of one entry for import.
     * @param string $wkType - the WIKINDX resource type for this bibtex entry
     * @param mixed $authors
     * @param mixed $editors
     *
     * @return int
     */
    /**
     * writeCreatorTable - write creator table and get lastAutoId
     *
     * @param mixed $authors
     * @param mixed $editors
     * @param string $wkType
     *
     * @return comma-separated list of creator IDs ready for insertion into WKX_resource_creator table.
     */
    private function writeCreatorTable($authors, $editors, $wkType)
    {
        $index = 1;
        $creatorArray = [];
        foreach ($authors as $array)
        {
            if ($cField = array_search('author', $this->map->{$wkType}['resource_creator']))
            {
                $creatorArray[$cField][$index]['prefix'] = $array[4];
                $creatorArray[$cField][$index]['surname'] = $array[2] . ' ' . $array[3];
                $creatorArray[$cField][$index]['firstname'] = $array[0];
                $creatorArray[$cField][$index]['initials'] = $array[1];
                ++$index;
            }
        }
        $index = 1;
        foreach ($editors as $array)
        {
            if ($cField = array_search('editor', $this->map->{$wkType}['resource_creator']))
            {
                $creatorArray[$cField][$index]['prefix'] = $array[4];
                $creatorArray[$cField][$index]['surname'] = $array[2] . ' ' . $array[3];
                $creatorArray[$cField][$index]['firstname'] = $array[0];
                $creatorArray[$cField][$index]['initials'] = $array[1];
                ++$index;
            }
        }
        if (!empty($creatorArray))
        {
            $this->deleteCacheCreators = TRUE;
        }
        // NB, even if array is empty, we need to write empty row to resource_creator
        $this->import->writeCreatorTables($creatorArray);
    }
    /**
     * writeCollectionTable - write WKX_collection table
     *
     * The only input from bibtex that can be a wikindx 'collection' is the 'journal' field or, for @inbook,
     * the 'booktitle' field.
     *
     * @param array $entry Assoc array of one entry for import.
     * @param string $wkType The WIKINDX resource type for this bibtex entry
     */
    private function writeCollectionTable($entry, $wkType)
    {
        $title = $short = FALSE;
        // inbook type with a chapter field that is numeric, bibtex field 'chapter' is wikindx's title, bibtex field 'title' is wikindx collectionTitle
        if ($wkType == 'book_chapter')
        {
            $title = \UTF8\mb_trim($entry['title']);
        }
        // This was originally bibtex @inbook, because the bibtex field chapter was nonnumeric, it's been converted to wikindx book_article.
        // bibtex field 'chapter' is wikindx's title, bibtex field 'title' is wikindx collectionTitle
        elseif (($wkType == 'book_article') && array_key_exists('chapter', $entry))
        {
            $title = \UTF8\mb_trim($entry['title']);
        }
        elseif (($wkType == 'book_article') && !array_key_exists('booktitle', $entry))
        {
            return;
        }
        elseif (array_key_exists('booktitle', $entry))
        {
            $title = \UTF8\mb_trim($entry['booktitle']);
        }
        if (!$title && !array_key_exists('journal', $entry))
        {
            return;
        }
        elseif (!$title && array_key_exists('journal', $entry))
        {
            $title = \UTF8\mb_trim($entry['journal']);
        }
        if (!$title)
        {
            return;
        }
        if (!empty($this->strings))
        {
            $short = array_search($title, $this->strings);
        }
        $this->collectionId = $this->import->writeCollectionTable($title, $short, $wkType);
        $this->deleteCacheCollections = TRUE;
    }
    /**
     * writePublisherTable - write WKX_publisher table
     *
     * @param array $entry Assoc array of one entry for import.
     * @param string $wkType The WIKINDX resource type for this bibtex entry
     */
    private function writePublisherTable($entry, $wkType)
    {
        $organization = $publisherName = $publisherLocation = $conferenceLocation = FALSE;
        if (array_key_exists('publisher', $entry))
        {
            $publisherName = \UTF8\mb_trim($entry['publisher']);
        }
        if (array_key_exists('organization', $entry) && ($wkType != 'proceedings_article'))
        {
            $publisherName = \UTF8\mb_trim($entry['organization']);
        }
        elseif (array_key_exists('organization', $entry))
        {
            $organization = \UTF8\mb_trim($entry['organization']);
        }
        elseif (array_key_exists('school', $entry))
        {
            $publisherName = \UTF8\mb_trim($entry['school']);
        }
        elseif (array_key_exists('institution', $entry))
        {
            $publisherName = \UTF8\mb_trim($entry['institution']);
        }
        if (!$organization && !$publisherName)
        {
            return;
        }
        if (array_key_exists('address', $entry) && ($wkType != 'proceedings_article'))
        {
            $publisherLocation = \UTF8\mb_trim($entry['address']);
        }
        elseif (array_key_exists('address', $entry))
        {
            $conferenceLocation = \UTF8\mb_trim($entry['address']);
        }
        if (array_key_exists('location', $entry))
        {
            if ($wkType == 'proceedings_article')
            {
                $conferenceLocation = \UTF8\mb_trim($entry['location']);
            }
            else
            {
                $publisherLocation = \UTF8\mb_trim($entry['location']);
            }
        }
        if ($wkType == 'proceedings_article')
        {
            $this->publisherId = $this->import->writePublisherTable($organization, $conferenceLocation, $wkType);
            $this->confPublisherId = $this->import->writePublisherTable($publisherName, $publisherLocation, $wkType);
        }
        else
        {
            $this->publisherId = $this->import->writePublisherTable($publisherName, $publisherLocation, $wkType);
        }
        $this->deleteCachePublishers = TRUE;
    }
    /**
     * writeResourceYearTable - write WKX_resource_year table
     *
     * @param array $entry Assoc array of one entry for import.
     * @param string $wkType The WIKINDX resource type for this bibtex entry
     */
    private function writeResourceYearTable($entry, $wkType)
    {
        foreach ($entry as $bibField => $bibValue)
        {
            if ($wkField = array_search($bibField, $this->map->{$wkType}['resource_year']))
            {
                $fields[] = $wkField;
                $values[] = $bibValue;
            }
        }
        if (!isset($fields))
        {
            return;
        }
        $this->import->writeYearTable($fields, $values);
    }
    /**
     * writeResourcePageTable - write WKX_resource_page table
     *
     * @param array $entry Assoc array of one entry for import.
     * @param string $wkType
     */
    private function writeResourcePageTable($entry, $wkType)
    {
        if (($wkType == 'book') || ($wkType == 'thesis'))
        {
            return; // numPages written in miscellaneous table.
        }
        if (!array_key_exists('pages', $entry))
        {
            return;
        }
        list($pageStart, $pageEnd) = $this->pages->init($entry['pages']);
        if ($pageStart)
        {
            $fields[] = 'resourcepagePageStart';
            $values[] = $pageStart;
        }
        if ($pageEnd)
        {
            $fields[] = 'resourcepagePageEnd';
            $values[] = $pageEnd;
        }
        if (!isset($fields))
        {
            return;
        }
        $this->import->writePageTable($fields, $values);
    }
    /**
     * writeResourceNoteTable - write WKX_resource_note table
     *
     * @param array $entry Assoc array of one entry for import.
     */
    private function writeResourcetextTable($entry)
    {
        $notes = $abstract = FALSE;
        if (array_key_exists('note', $entry))
        {
            $notes = $entry['note'];
        }
        elseif (array_key_exists('annote', $entry))
        {
            $notes = $entry['annote'];
        }
        if (array_key_exists('abstract', $entry))
        {
            $abstract = $entry['abstract'];
        }
        if (!$notes && !$abstract && !$this->url)
        {
            return;
        }
        $this->import->writeResourcetextTable($notes, $abstract, $this->url);
    }
    /**
     * writeResourceKeywordTable - write WKX_resource_keyword table
     *
     * @param array $entry Assoc array of one entry for import.
     */
    private function writeResourceKeywordTable($entry)
    {
        if (array_key_exists('import_KeywordIgnore', $this->formData))
        {
            return;
        }
        if (!array_key_exists('keywords', $entry))
        {
            return;
        }
        if (!array_key_exists('import_KeywordSeparator', $this->formData))
        {
            $separator = '1'; // default semicolon
        }
        else
        {
            $separator = $this->formData['import_KeywordSeparator'];
        }
        if ($separator == 0)
        {
            $keywords = preg_split("/,/u", \UTF8\mb_trim($entry['keywords']));
        }
        elseif ($separator == 1)
        {
            $keywords = preg_split("/;/u", \UTF8\mb_trim($entry['keywords']));
        }
        elseif ($separator == 2)
        {
            $keywords = preg_split("/;|,/u", \UTF8\mb_trim($entry['keywords']));
        }
        else
        {
            $keywords = preg_split("/ /u", \UTF8\mb_trim($entry['keywords']));
        }
        foreach ($keywords as $keyword)
        {
            $keyword = \HTML\stripHtml(\UTF8\mb_trim($keyword));
            if (!$keyword)
            {
                continue;
            }
            $tempK[] = $keyword;
        }
        if (!isset($tempK))
        {
            return;
        }
        $keywords = array_unique($tempK);
        if (array_key_exists('keywords', $entry) && \UTF8\mb_trim($entry['keywords']))
        {
            $this->import->writeKeywordTables($keywords);
            $this->deleteCacheKeywords = TRUE;
        }
    }
    /**
     * writeBibtexStringTable - write $this->strings to bibtex_string table
     */
    private function writeBibtexStringTable()
    {
        if (!empty($this->strings) && array_key_exists("import_Raw", $this->formData))
        {
            $fields[] = 'bibtexstringText';
            foreach ($this->strings as $key => $value)
            {
                $raw[] = '@STRING{' . $key . '=' . $value . '}';
            }
            $values[] = base64_encode(serialize(implode("\n", $raw)));
            $this->db->insert('bibtex_string', $fields, $values);
            $this->bibtexStringId = $this->db->lastAutoId();
        }
    }
    /**
     * grabHowPublished - check for type of howpublished field in bibtex misc entry
     *
     * @param array $entry Assoc array of one entry for import.
     *
     * @return array array(URL, howPublished)
     */
    private function grabHowPublished($entry)
    {
        $url = $howPublished = FALSE;
        if (($entry['bibtexEntryType'] == 'misc') && array_key_exists('howpublished', $entry))
        {
            if (preg_match("#^\\\\url{(.*://.*)}#u", $entry['howpublished'], $match))
            {
                $url = $match[1];
            }
            else
            {
                $howPublished = $entry['howpublished'];
            }
        }
        elseif (array_key_exists('url', $entry))
        {
            if (preg_match("#^\\\\url{(.*://.*)}#u", $entry['url'], $match))
            {
                $url = $match[1];
            }
            else
            {
                $url = $entry['url'];
            }
        }

        return [$url, $howPublished];
    }
    /**
     * grabMonth - check for any month field and split into component day/month fields
     *
     * @param array $entry Assoc array of one entry for import.
     *
     * @return array array(startMonth, startDay, endMonth, endDay)
     */
    private function grabMonth($entry)
    {
        $startMonth = $startDay = $endMonth = $endDay = FALSE;
        if (array_key_exists('month', $entry))
        {
            list($startMonth, $startDay, $endMonth, $endDay) = $this->monthObj->init($entry['month']);
        }

        return [$startMonth, $startDay, $endMonth, $endDay];
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
        if (array_key_exists('import_Raw', $this->vars) && $this->vars['import_Raw'])
        {
            $this->formData["import_Raw"] = $this->vars['import_Raw'];
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
        $this->formData["import_KeywordSeparator"] = $this->vars['import_KeywordSeparator'];
        $this->formData["import_TitleSubtitleSeparator"] = $this->vars['import_TitleSubtitleSeparator'];
        if (array_key_exists('import_Tag', $this->vars) && \UTF8\mb_trim($this->vars['import_Tag']))
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
        if (($this->type == 'file') && !array_key_exists("import_UnrecognisedFields", $this->formData))
        {
            if (!$this->importFile)
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
            }
            else
            { // An import from a plug-in like ImportPubMed
                $fileName = $this->importFile;
            }
            $this->formData['import_File'] = $fileName;
            $this->garbageFiles[implode(DIRECTORY_SEPARATOR, [$this->dirName, $fileName])] = FALSE;
            if ($error)
            {
                $this->badInput->close($error, $this->badClass, [$this->badFunction, $this->formData]);
            }

            return implode(DIRECTORY_SEPARATOR, [$this->dirName, $fileName]);
        }
        elseif ($this->type == 'paste')
        {
            if (!\UTF8\mb_trim($this->vars['import_Paste']))
            {
                $error = $this->errors->text("inputError", "missing");
            }
            else
            {
                $pasteInput = stripslashes(\UTF8\mb_trim($this->vars['import_Paste']));
                $this->formData["import_Paste"] = base64_encode($pasteInput);
                list($fileName, $fullFileName) = FILE\createFileName($this->dirName, $pasteInput, '.bib');
                if (!$fullFileName)
                {
                    $error = $this->errors->text("file", "write", ": $fileName");
                }
                if ($fp = fopen("$fullFileName", "w"))
                {
                    if (!fwrite($fp, $pasteInput))
                    {
                        $error = $this->errors->text("file", "write", ": $fileName");
                    }
                    fclose($fp);
                }
                else
                {
                    $error = $this->errors->text("file", "write", ": $fileName");
                }
            }
            if ($error)
            {
                $this->badInput->close($error, $this->badClass, [$this->badFunction, $this->formData]);
            }
            $this->garbageFiles[$fullFileName] = FALSE;

            return $fullFileName;
        }
    }
    /**
     * convertEntries - convert any laTeX code and convert to UTF-8 ready for storing in the database
     *
     * @param array $entries - multidimensional array of entries
     *
     * @return array multidimensional array of converted entries.
     */
    private function convertEntries($entries)
    {
        foreach ($this->bibConfig->bibtexSpCh as $key => $value)
        {
            $replaceBibtex[] = \UTF8\mb_chr($key);
            $matchBibtex[] = preg_quote("/$value/u");
        }
        foreach ($this->bibConfig->bibtexSpChOld as $key => $value)
        {
            $replaceBibtex[] = \UTF8\mb_chr($key);
            $matchBibtex[] = preg_quote("/$value/u");
        }
        foreach ($this->bibConfig->bibtexSpChOld2 as $key => $value)
        {
            $replaceBibtex[] = \UTF8\mb_chr($key);
            $matchBibtex[] = preg_quote("/$value/u");
        }
        foreach ($this->bibConfig->bibtexSpChLatex as $key => $value)
        {
            $replaceBibtex[] = \UTF8\mb_chr($key);
            $matchBibtex[] = preg_quote("/$value/u");
        }
        foreach ($this->bibConfig->bibtexWordsTranslate as $key => $value)
        { // NB reverse key--value
            $replaceBibtex[] = $value;
            $matchBibtex[] = preg_quote("/$key/u");
        }
        foreach ($this->bibConfig->bibtexCodesDelete as $key => $value)
        { // NB reverse key--value
            $replaceBibtex[] = $value;
            $matchBibtex[] = preg_quote("/$key/u");
        }
        $index = 0;
        foreach ($entries as $eKey => $array)
        {
            if (!is_array($array))
            { // e.g. strings array
                $temp[$eKey] = stripslashes(\UTF8\smartUtf8_encode(preg_replace($matchBibtex, $replaceBibtex, $array)));

                continue;
            }
            foreach ($array as $key => $value)
            {
                $temp[$index][$key] = stripslashes(\UTF8\smartUtf8_encode(preg_replace($matchBibtex, $replaceBibtex, $value)));
            }
            $index++;
        }
        if (!isset($temp))
        {
            $this->badInput->close($this->errors->text("inputError", "invalid"), $this->badClass, [$this->badFunction, $this->formData]);
        }

        return $temp;
    }
}
