<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * RESOURCEFORM -- Enter or edit a new resource via a form
 */
class RESOURCEFORM
{
    public $seriesFill = FALSE;
    public $translationFill = FALSE;
    public $collectionFill = FALSE;
    private $db;
    private $vars;
    private $messages;
    private $session;
    private $type;
    private $gatekeep;
    private $sessionVars = [];
    private $resourceMap;
    private $typeMaps;
    private $resourceType = 'book'; // default for new entry
    private $category;
    private $keyword;
    private $userTagObj;
    private $publisher;
    private $collection;
    private $collectionMap;
    private $publisherMap;
    private $creator;
    private $commonBib;
    private $eds = [];
    private $tdLabelWidth = 'width15percent';
    private $tdContentWidth = 'width85percent';
    private $edit = FALSE;
    private $creatorsArray = [];
    private $collectionDefaults = [];
    private $collectionDefaultMap;
    private $collectionType = FALSE;
    private $error = FALSE;
    private $tinymce;

    public function __construct()
    {
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->gatekeep->init();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->session = FACTORY_SESSION::getInstance();
        $this->config = FACTORY_CONFIG::getInstance();

        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->type = FACTORY_TYPE::getInstance();
        $this->resourceMap = FACTORY_RESOURCEMAP::getInstance();

        $this->category = FACTORY_CATEGORY::getInstance();
        $this->keyword = FACTORY_KEYWORD::getInstance();
        $this->userTagObj = FACTORY_USERTAGS::getInstance();
        $this->publisher = FACTORY_PUBLISHER::getInstance();
        $this->collection = FACTORY_COLLECTION::getInstance();
        $this->collectionMap = FACTORY_COLLECTIONMAP::getInstance();
        $this->publisherMap = FACTORY_PUBLISHERMAP::getInstance();
        $this->creator = FACTORY_CREATOR::getInstance();
        $this->commonBib = FACTORY_BIBLIOGRAPHYCOMMON::getInstance();
        $this->tinymce = FACTORY_LOADTINYMCE::getInstance();
        $this->typeMaps = $this->resourceMap->getTypeMap();
        $this->loadElementDefinitions();
        $this->session->delVar('resourceLock');
    }
    /**
     * Start the process of entering a new resource by asking for the choice of resource type and number of authors
     *
     * @param string|FALSE $error
     */
    public function init($error = FALSE)
    {
        // type stored in $this->sessionVars['resourceType'] might have been disabled in the Admin|Configure interface
        if (array_key_exists('resourceType', $this->sessionVars) && !array_key_exists($this->sessionVars['resourceType'], $this->typeMaps))
        {
            $aKeys = array_keys($this->typeMaps);
            $this->resourceType = $this->sessionVars['resourceType'] = array_shift($aKeys);
        }
        if (!$error)
        {
            $this->session->clearArray('resourceForm');
            $this->session->clearArray('resourceFormValidate');
            $this->sessionVars = [];
        }
        else
        {
            $this->error = TRUE;
            if (!$this->sessionVars = $this->session->getArray('resourceForm'))
            {
                $this->sessionVars = [];
            }
            $this->resourceType = array_key_exists("resourceType", $this->sessionVars) ? $this->sessionVars['resourceType'] : FALSE;
        }
        if (array_key_exists('type', $this->vars) && ($this->vars['type'] == 'edit'))
        {
            GLOBALS::setTplVar('heading', $this->messages->text('heading', 'editResource'));
            if (!$error)
            {
                $this->getEditSession();
            }
            $this->session->setVar('resourceFormType', 'edit');
        }
        else
        {
            GLOBALS::setTplVar('heading', $this->messages->text('heading', 'newResource'));
            $this->session->setVar('resourceFormType', 'new');
        }
        $pString = \HTML\p($this->messages->text('resources', 'new', '&nbsp;' . $this->required()));
        $pString .= \HTML\hr();
        $pString .= $this->tinymce->loadBasicTextinput(['resourceTitle', 'resourceSubtitle'], 400);
        $pString .= $this->tinymce->loadMetadataTextarea(['resourcetextAbstract', 'resourcetextNote']);
        $requiredArray = \AJAX\encode_jArray($this->resourceMap->getRequired(), TRUE);
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=validate';
        $jsonArray[] = [
            'startFunction' => 'validate',
            'script' => "$jScript",
            'targetDiv' => 'validateOuter',
            'requiredArray' => "$requiredArray",
        ];
        $js = \AJAX\jActionForm('onsubmit', $jsonArray, TRUE);
        $pString .= \FORM\formHeaderName('resource_RESOURCEWRITE_CORE', 'resourceForm', $js);
        if ($this->edit)
        {
            $pString .= \FORM\hidden('resourceId', $this->vars['id']);
        }
        // resource type and title
        $pString .= $this->typeTitleCell();
        // optional cells
        $pString .= $this->optionalCells();
        // creators
        $array = [];
        if (array_key_exists('resourceType', $this->sessionVars))
        {
            if (array_key_exists('resourcecreator', $this->typeMaps[$this->sessionVars['resourceType']]))
            {
                $array = $this->typeMaps[$this->sessionVars['resourceType']]['resourcecreator'];
            }
        }
        else
        {
            $array = $this->typeMaps[$this->resourceType]['resourcecreator']; // default when 'new resource' selected
        }
        if (!empty($array))
        {
            $cell = \HTML\tableStart('generalTable borderStyleEmphasis');
            foreach ($array as $key => $creatorMsg)
            {
                $cell .= $this->blankCreatorCell($key, $creatorMsg);
            }
            $cell .= \HTML\tableEnd();
            // Creator tables all wrapped up in 'creatorsOuter' DIV which is hidden if no creators selected or available for this resource type.
            $pString .= \HTML\div('creatorsOuter', $cell);
        }
        else
        {
            $pString .= \HTML\div('creatorsOuter', FALSE);
        }
        // custom fields
        $pString .= $this->customCells();
        // Abstract, notes, categories, keywords etc.
        $pString .= $this->otherCell();
        // Must be here but is initially hidden
        $pString .= \HTML\div('validateOuter', $error, 'error');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Save")));

        $pString .= \FORM\formEnd();

        GLOBALS::addTplVar('content', $pString);
        // Load at end because .js initialization needs various DIVs to be in the page before they are made invisible
        \AJAX\loadJavascript($this->config->WIKINDX_BASE_URL . '/core/modules/resource/resourceForm.js');
    }
    /**
     * Print optional cells such as translated works, collection, publisher
     *
     * @param mixed $cellFunction
     *
     * @return string
     */
    public function optionalCells($cellFunction = FALSE)
    {
        if ($cellFunction && array_key_exists($cellFunction, $this->typeMaps[$this->resourceType]['optional']) &&
            method_exists($this, $cellFunction))
        {
            return $this->{$cellFunction}();
        }
        elseif ($cellFunction)
        {
            if ($cellFunction == 'publisher')
            {
                return $this->blankPublisher();
            }
            elseif ($cellFunction == 'conference')
            {
                return $this->blankConference();
            }
            elseif ($cellFunction == 'series')
            {
                return $this->blankSeries();
            }
            elseif ($cellFunction == 'collection')
            {
                return $this->blankCollection();
            }
            elseif ($cellFunction == 'translation')
            {
                return $this->blankTranslation();
            }
            elseif ($cellFunction == 'miscellaneous')
            {
                return $this->blankMiscellaneous();
            }
        }
        $pString = '';
        foreach ($this->resourceMap->getOptional() as $cellFunction)
        {
            if (array_key_exists($cellFunction, $this->typeMaps[$this->resourceType]['optional']) &&
                method_exists($this, $cellFunction))
            {
                $pString .= $this->{$cellFunction}();
            }
            elseif ($cellFunction == 'publisher')
            {
                $pString .= $this->blankPublisher();
            }
            elseif ($cellFunction == 'conference')
            {
                $pString .= $this->blankConference();
            }
            elseif ($cellFunction == 'series')
            {
                $pString .= $this->blankSeries();
            }
            elseif ($cellFunction == 'collection')
            {
                $pString .= $this->blankCollection();
            }
            elseif ($cellFunction == 'translation')
            {
                $pString .= $this->blankTranslation();
            }
            elseif ($cellFunction == 'miscellaneous')
            {
                $pString .= $this->blankMiscellaneous();
            }
        }

        return $pString;
    }
    /**
     * Publisher
     *
     * @return string
     */
    public function publisher()
    {
        $insert = $this->publisherInsert();
        if (empty($insert))
        {
            $insert = '';
        }
        $insertAfter = $insertBefore = [];
        if (array_key_exists('insertBefore', $this->typeMaps[$this->resourceType]['optional']['publisher']))
        {
            $insertBefore[$this->typeMaps[$this->resourceType]['optional']['publisher']['insertBefore']] = $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['publisher']['insertBefore']);
        }
        if (array_key_exists('insertAfter', $this->typeMaps[$this->resourceType]['optional']['publisher']))
        {
            $insertAfter[$this->typeMaps[$this->resourceType]['optional']['publisher']['insertAfter']] = $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['publisher']['insertAfter']);
        }

        return $this->optionalCellsFormat('publisher', 'publisherOuter', $insertBefore, $insertAfter, 'publisher');
    }
    /**
     * For book, book_article, book_chapter, translated work details
     *
     * @return string
     */
    public function translation()
    {
        if (($this->resourceType != 'book') && ($this->resourceType != 'book_article') && ($this->resourceType != 'book_chapter'))
        {
            return '';
        }
        if ($this->translationFill)
        {
            list($titInsert, $pubInsert) = $this->translationInsertFill();
        }
        else
        {
            $pubInsert = $this->translationPubInsert();
            $titInsert = $this->translationTitleInsert();
        }
        if (!$pubInsert)
        {
            $pubInsert = '';
        }
        if (!$titInsert)
        {
            $titInsert = '';
        }
        $insertAfter = $insertBefore = [];
        // In RESOURCEMAP, we stipulate that insert_after is used for title select box and insert_before for publisher select box
        if (array_key_exists('insertBefore', $this->typeMaps[$this->resourceType]['optional']['translation']))
        {
            $insertBefore[$this->typeMaps[$this->resourceType]['optional']['translation']['insertBefore']] = $pubInsert;
            unset($this->typeMaps[$this->resourceType]['optional']['translation']['insertBefore']);
        }
        if (array_key_exists('insertAfter', $this->typeMaps[$this->resourceType]['optional']['translation']))
        {
            $insertAfter[$this->typeMaps[$this->resourceType]['optional']['translation']['insertAfter']] = $titInsert;
            unset($this->typeMaps[$this->resourceType]['optional']['translation']['insertAfter']);
        }

        return $this->optionalCellsFormat('translation', 'translationOuter', $insertBefore, $insertAfter, 'translatedFrom');
    }
    /**
     * Series
     *
     * @return string
     */
    public function series()
    {
        if ($this->collectionFill && array_key_exists('series', $this->sessionVars))
        {
            $this->seriesFill = $this->sessionVars['series'];
            $insert = $this->seriesInsertFill();
        }
        elseif ($this->seriesFill)
        {
            $insert = $this->seriesInsertFill();
        }
        else
        {
            $insert = $this->seriesInsert();
        }
        if (!$insert)
        {
            $insert = '';
        }
        $insertAfter = $insertBefore = [];
        if (array_key_exists('insertBefore', $this->typeMaps[$this->resourceType]['optional']['series']))
        {
            $insertBefore[$this->typeMaps[$this->resourceType]['optional']['series']['insertBefore']] = $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['series']['insertBefore']);
        }
        if (array_key_exists('insertAfter', $this->typeMaps[$this->resourceType]['optional']['series']))
        {
            $insertAfter[$this->typeMaps[$this->resourceType]['optional']['series']['insertAfter']] = $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['series']['insertAfter']);
        }

        return $this->optionalCellsFormat('series', 'seriesOuter', $insertBefore, $insertAfter, 'series');
    }
    /**
     * Conference
     *
     * @return string
     */
    public function conference()
    {
        if ($this->collectionFill !== FALSE)
        {
            $this->sessionVars['conferenceId'] = $this->collectionFill;
            $insert = $this->conferenceInsert();
        }
        else
        {
            $insert = $this->conferenceInsert();
        }
        if (empty($insert))
        {
            $insert = '';
        }
        $insertAfter = $insertBefore = [];
        if (array_key_exists('insertBefore', $this->typeMaps[$this->resourceType]['optional']['conference']))
        {
            $insertBefore[$this->typeMaps[$this->resourceType]['optional']['conference']['insertBefore']] =
                $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['conference']['insertBefore']);
        }
        if (array_key_exists('insertAfter', $this->typeMaps[$this->resourceType]['optional']['conference']))
        {
            $insertAfter[$this->typeMaps[$this->resourceType]['optional']['conference']['insertAfter']] =
                $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['conference']['insertAfter']);
        }

        return $this->optionalCellsFormat('conference', 'conferenceOuter', $insertBefore, $insertAfter, 'conference');
    }
    /**
     * Get collection defaults when a collection is chosen from the collection select box.
     *
     * Called from RESOURCEFORM\AJAX\fillPublisher() if
     * 'fromCollection' is present in $this->vars. Load defaults into $sessionVars.
     */
    public function getCollectionDefaults()
    {
        include_once("core/collection/COLLECTIONDEFAULTMAP.php");
        $this->collectionDefaultMap = new COLLECTIONDEFAULTMAP();
        $this->db->formatConditions(['collectionId' => $this->collectionFill]);
        $recordset = $this->db->select('collection', ['collectionType', 'collectionDefault']);
        $row = $this->db->fetchRow($recordset);
        if ($row['collectionDefault'])
        {
            $this->collectionDefaults = unserialize(base64_decode($row['collectionDefault']));
            $this->collectionType = $row['collectionType'];
            foreach ($this->collectionDefaultMap->{$this->collectionType}['resource'] as $key => $value)
            {
                $field = 'resource' . $key;
                if (array_key_exists($field, $this->collectionDefaults))
                {
                    if ($key == 'Field1')
                    { // seriesTitle
                        $this->sessionVars['series'] = base64_encode($this->collectionDefaults[$field]);
                    }
                    else
                    {
                        $this->sessionVars[$field] = $this->collectionDefaults[$field];
                    }
                }
                else
                {
                    $this->session->delVar($field);
                }
            }
            foreach ($this->collectionDefaultMap->{$this->collectionType}['resource_year'] as $key => $value)
            {
                $field = 'resourceyear' . $key;
                if (array_key_exists($field, $this->collectionDefaults))
                {
                    $this->sessionVars[$field] = $this->collectionDefaults[$field];
                }
                else
                {
                    $this->session->delVar($field);
                }
            }
            foreach ($this->collectionDefaultMap->{$this->collectionType}['resource_misc'] as $key => $value)
            {
                $field = 'resourcemisc' . $key;
                if (array_key_exists($field, $this->collectionDefaults))
                {
                    if (($field == 'resourcemiscPublisher') && ($this->collectionType == 'proceedings'))
                    {
                        $this->sessionVars['organizerId'] = $this->collectionDefaults[$field];
                    }
                    elseif (($field == 'resourcemiscField1') && ($this->collectionType == 'proceedings'))
                    {
                        $this->sessionVars['publisherId'] = $this->collectionDefaults[$field];
                    }
                    elseif ($field == 'resourcemiscPublisher')
                    {
                        $this->sessionVars['publisherId'] = $this->collectionDefaults[$field];
                    }
                    elseif ($field == 'resourcemiscField1')
                    { // trans publisher
                        $this->sessionVars['transPublisherId'] = $this->collectionDefaults[$field];
                    }
                    elseif (($field == 'resourcemiscPeerReviewed') && ($this->collectionDefaults[$field] == 'N'))
                    {
                        $this->session->delVar($field);
                    }
                    else
                    {
                        $this->sessionVars[$field] = $this->collectionDefaults[$field];
                    }
                }
                else
                {
                    $this->session->delVar($field);
                }
            }
            if (array_key_exists('creators', $this->collectionDefaults))
            {
                foreach ($this->collectionDefaults['creators'] as $key => $value)
                {
                    if ($value)
                    {
                        $this->sessionVars[$key] = $value;
                    }
                }
            }
        }
    }
    /**
     * Collection
     *
     * @return string
     */
    public function collection()
    {
        $insert = $this->collectionInsert();
        if (empty($insert))
        {
            $insert = '';
        }
        $insertAfter = $insertBefore = [];
        if (array_key_exists('insertBefore', $this->typeMaps[$this->resourceType]['optional']['collection']))
        {
            $insertBefore[$this->typeMaps[$this->resourceType]['optional']['collection']['insertBefore']] =
                $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['collection']['insertBefore']);
        }
        if (array_key_exists('insertAfter', $this->typeMaps[$this->resourceType]['optional']['collection']))
        {
            $insertAfter[$this->typeMaps[$this->resourceType]['optional']['collection']['insertAfter']] =
                $insert;
            unset($this->typeMaps[$this->resourceType]['optional']['collection']['insertAfter']);
        }

        return $this->optionalCellsFormat('collection', 'organizerOuter', $insertBefore, $insertAfter, 'collection');
    }
    /**
     * Miscellaneous details -- may not have 'insertBefore' or 'insertAfter'
     *
     * @return string
     */
    public function miscellaneous()
    {
        $insertAfter = $insertBefore = []; // empty arrays
        if (array_key_exists('insertBefore', $this->typeMaps[$this->resourceType]['optional']['miscellaneous']))
        {
            unset($this->typeMaps[$this->resourceType]['optional']['miscellaneous']['insertBefore']);
        }
        if (array_key_exists('insertAfter', $this->typeMaps[$this->resourceType]['optional']['miscellaneous']))
        {
            unset($this->typeMaps[$this->resourceType]['optional']['miscellaneous']['insertAfter']);
        }

        return $this->optionalCellsFormat('miscellaneous', 'miscellaneousOuter', $insertBefore, $insertAfter, 'miscellaneous');
    }
    /**
     * Populated creator cells -- used for new resource entry
     *
     * @param mixed $type
     * @param mixed $inputArray
     *
     * @return array|FALSE
     */
    public function creatorFields($type, $inputArray)
    {
        $fields = $label = '';
        if (empty($this->creatorsArray))
        {
            $temp = $this->creator->grabAll();
            $this->creatorsArray[0] = $this->messages->text("misc", "ignore");
            if (is_array($temp))
            {
                $this->creatorsArray = $this->creatorsArray + $temp;
            }
        }
        $this->writeSessionCreators($inputArray);
        for ($index = 0; $index < $inputArray['index']; $index++)
        {
            if ($index == 0)
            {
                $label = $this->makeCreatorLabel();
            }
            $this->session->setVar('resourceFormValidate_' . $type . '_' . $index, TRUE);
            $entry = $type . '_' . $index . '_firstname';
            $text = array_key_exists($entry, $this->sessionVars) ? $this->sessionVars[$entry] : FALSE;
            $fields .= \HTML\trStart();
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 30, 255));
            $entry = $type . '_' . $index . '_initials';
            $text = array_key_exists($entry, $this->sessionVars) ? $this->sessionVars[$entry] : FALSE;
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 6, 255));
            $entry = $type . '_' . $index . '_prefix';
            $text = array_key_exists($entry, $this->sessionVars) ? $this->sessionVars[$entry] : FALSE;
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 11, 255));
            $entry = $type . '_' . $index . '_surname';
            $text = array_key_exists($entry, $this->sessionVars) ? $this->sessionVars[$entry] : FALSE;
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 30, 255));
            $entry = $type . '_' . $index . '_select';
            $selected = array_key_exists($entry, $this->sessionVars) ? $this->sessionVars[$entry] : FALSE;
            if ($selected)
            {
                $fields .= \HTML\td(\FORM\selectedBoxValue(FALSE, $entry, $this->creatorsArray, $selected, 1));
            }
            else
            {
                $fields .= \HTML\td(\FORM\selectFBoxValue(FALSE, $entry, $this->creatorsArray, 1));
            }
            $fields .= \HTML\trEnd();
        }
        if (!$label)
        {
            return FALSE;
        }
        else
        {
            return $label . $fields;
        }
    }
    /**
     * Populated creator cells -- used for editing a resource entry
     *
     * @param mixed $type
     *
     * @return array
     */
    public function creatorFieldsEdit($type)
    {
        $fields = $label = '';
        if (empty($this->creatorsArray))
        {
            $temp = $this->creator->grabAll();
            $this->creatorsArray[0] = $this->messages->text("misc", "ignore");
            if (is_array($temp))
            {
                $this->creatorsArray = $this->creatorsArray + $temp;
            }
        }
        for ($index = 0; ; $index++)
        {
            $found = FALSE;
            $thisRow = FALSE;
            $select = $type . '_' . $index . '_select';
            if (!array_key_exists($select, $this->sessionVars) && !$this->error)
            {
                break;
            }
            $this->session->setVar('resourceFormValidate_' . $type . '_' . $index, TRUE);
            $entry = $type . '_' . $index . '_firstname';
            $value = $this->session->getVar('resourceForm_' . $entry) ? $this->session->getVar('resourceForm_' . $entry) : FALSE;
            $found = $value ? TRUE : FALSE;
            $thisRow .= \HTML\trStart();
            $thisRow .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 30, 255));
            $entry = $type . '_' . $index . '_initials';
            $value = $this->session->getVar('resourceForm_' . $entry) ? $this->session->getVar('resourceForm_' . $entry) : FALSE;
            $found = $value ? TRUE : FALSE;
            $thisRow .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 6, 255));
            $entry = $type . '_' . $index . '_prefix';
            $value = $this->session->getVar('resourceForm_' . $entry) ? $this->session->getVar('resourceForm_' . $entry) : FALSE;
            $found = $value ? TRUE : FALSE;
            $thisRow .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 11, 255));
            $entry = $type . '_' . $index . '_surname';
            $value = $this->session->getVar('resourceForm_' . $entry) ? $this->session->getVar('resourceForm_' . $entry) : FALSE;
            $found = $value ? TRUE : FALSE;
            $thisRow .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 30, 255));
            if (array_key_exists($select, $this->sessionVars))
            {
                $found = TRUE;
                $thisRow .= \HTML\td(\FORM\selectedBoxValue(
                    FALSE,
                    $select,
                    $this->creatorsArray,
                    $this->sessionVars[$select],
                    1
                ));
            }
            else
            {
                $thisRow .= \HTML\td(\FORM\selectFBoxValue(FALSE, $select, $this->creatorsArray, 1));
            }
            $thisRow .= \HTML\trEnd();
            if (!$found)
            {
                break;
            }
            else
            {
                $fields .= $thisRow;
            }
            if ($index == 0)
            {
                $label = $this->makeCreatorLabel();
            }
        }
        if (!$label)
        {
            return [$index, \HTML\tableStart('generalTable borderStyleSolid') . FALSE . \HTML\tableEnd()];
        }

        return [$index, \HTML\tableStart('generalTable borderStyleSolid') . $label . $fields . \HTML\tableEnd()];
    }
    /**
     * Blank creator cells
     *
     * @param mixed $key
     * @param mixed $creatorMsg
     *
     * @return array
     */
    public function blankCreatorCell($key, $creatorMsg)
    {
        $jsonArray = [];
        $jScript = "index.php?action=resource_RESOURCEFORMAJAX_CORE&method=addCreatorField&creatorType=$key";
        if ($this->edit || $this->error || $this->collectionFill || ($this->session->getVar('resourceFormType') == 'edit'))
        {
            list($index, $editCell) = $this->creatorFieldsEdit($key);
            $jsonArray[] = [
                'startFunction' => 'addCreator',
                'script' => "$jScript",
                'creatorType' => "$key",
                'type' => 'edit',
                'index' => "$index",
            ];
        }
        else
        {
            $jsonArray[] = [
                'startFunction' => 'addCreator',
                'script' => "$jScript",
                'creatorType' => "$key",
                'type' => 'new',
            ];
        }
        $addImage = \AJAX\jActionIcon('add', 'onclick', $jsonArray);
        $jsonArray = [];
        $jScript = "index.php?action=resource_RESOURCEFORMAJAX_CORE&method=removeCreatorField&creatorType=$key";
        if ($this->edit || $this->error || $this->collectionFill || ($this->session->getVar('resourceFormType') == 'edit'))
        {
            $jsonArray[] = [
                'startFunction' => 'removeCreator',
                'script' => "$jScript",
                'creatorType' => "$key",
                'type' => 'edit',
                'index' => "$index",
            ];
        }
        else
        {
            $jsonArray[] = [
                'startFunction' => 'removeCreator',
                'script' => "$jScript",
                'creatorType' => "$key",
                'type' => 'new',
            ];
        }
        $removeImage = \AJAX\jActionIcon('remove', 'onclick', $jsonArray);
        $images = '&nbsp;&nbsp;' . $addImage . '&nbsp;&nbsp;' . $removeImage;
        $creatorCells = \HTML\trStart();
        $creatorCells .= \HTML\td(\HTML\h($this->messages->text('creators', $creatorMsg) . $images, FALSE, 4), $this->tdLabelWidth);
        if ($this->edit || $this->error || $this->collectionFill || ($this->session->getVar('resourceFormType') == 'edit'))
        {
            $creatorCells .= \HTML\td(\HTML\div($key . '_Inner', $editCell), $this->tdContentWidth);
        }
        else
        {
            $creatorCells .= \HTML\td(\HTML\div($key . '_Inner', '&nbsp;'), $this->tdContentWidth);
        }
        $creatorCells .= \HTML\trEnd();

        return $creatorCells;
    }
    /**
     * Blank collection DIV
     *
     * @return string
     */
    public function blankCollection()
    {
        return \HTML\div('organizerOuter', FALSE);
    }
    /**
     * Blank series DIV
     *
     * @return string
     */
    public function blankSeries()
    {
        return \HTML\div('seriesOuter', FALSE);
    }
    /**
     * Blank conference DIV
     *
     * @return string
     */
    public function blankConference()
    {
        return \HTML\div('conferenceOuter', FALSE);
    }
    /**
     * Blank translation DIV
     *
     * @return string
     */
    public function blankTranslation()
    {
        return \HTML\div('translationOuter', FALSE);
    }
    /**
     * Blank publisher DIV
     *
     * @return string
     */
    public function blankPublisher()
    {
        return \HTML\div('publisherOuter', FALSE);
    }
    /**
     * Blank miscellaneous DIV
     *
     * @return string
     */
    public function blankMiscellaneous()
    {
        return \HTML\div('miscellaneousOuter', FALSE);
    }
    /**
     * DIV for ISBN/ID
     *
     * @return string
     */
    public function divIsbn()
    {
        $text = array_key_exists('resourceIsbn', $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourceIsbn']) : FALSE;

        return \HTML\div('isbnOuter', \FORM\textInput(
            $this->messages->text("resources", "isbn"),
            "resourceIsbn",
            $text,
            30,
            255
        ));
    }
    /**
     * DIV for DOI
     *
     * @return string
     */
    public function divDoi()
    {
        $text = array_key_exists('resourceDoi', $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourceDoi']) : FALSE;

        return \HTML\div('doiOuter', \FORM\textInput(
            $this->messages->text("resources", "doi"),
            "resourceDoi",
            $text,
            30,
            255
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "doi")
        ), 'hint'));
    }
    /**
     * Make select box for subcategories
     *
     * @param mixed $temp
     *
     * @return string
     */
    public function subcategoryBox($temp)
    {
        if (is_array($temp))
        {
            $selectedSubcategories = [];
            if (array_key_exists('resourcecategorySubcategories', $this->sessionVars))
            {
                $selected = UTF8::mb_explode(',', $this->sessionVars['resourcecategorySubcategories']);
                foreach ($selected as $key)
                {
                    $selectedSubcategories[$key] = $temp[$key];
                    unset($temp[$key]);
                }
            }
            $td = \FORM\selectFBoxValueMultiple(
                $this->messages->text('resources', 'subcategories'),
                "availableSubcategory",
                $temp,
                5
            ) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint');
            list($toBottomImage, $toTopImage) = $this->category->transferArrowsSubcatUpDown();
            $td .= \HTML\p($toBottomImage . '&nbsp;&nbsp;' . $toTopImage);
            $td .= \HTML\p(\FORM\selectFBoxValueMultiple(
                $this->messages->text('search', 'subcategory'),
                "resourcecategorySubcategories",
                $selectedSubcategories,
                5
            ) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint'));

            return \HTML\td(\HTML\div('subcategory', $td, 'width33percent'));

            $subcategories[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value)
            {
                $subcategories[$key] = $value;
            }
            if (array_key_exists('resourcecategorySubcategories', $this->sessionVars))
            {
                $selected = UTF8::mb_explode(',', $this->sessionVars['resourcecategorySubcategories']);

                return \HTML\td(\HTML\div(
                    'subcategory',
                    \FORM\selectedBoxValueMultiple(
                        $this->messages->text('resources', 'subcategories'),
                        'resourcecategorySubcategories',
                        $subcategories,
                        $selected,
                        5
                    ) . BR . \HTML\span(\HTML\aBrowse(
                        'green',
                        '',
                        $this->messages->text("hint", "hint"),
                        '#',
                        "",
                        $this->messages->text("hint", "multiples")
                    ), 'hint')
                ), 'width20percent');
            }
            else
            {
                return \HTML\td(\HTML\div(
                    'subcategory',
                    \FORM\selectFBoxValueMultiple(
                        $this->messages->text('resources', 'subcategories'),
                        'resourcecategorySubcategories',
                        $subcategories,
                        5
                    ) . BR . \HTML\span(\HTML\aBrowse(
                        'green',
                        '',
                        $this->messages->text("hint", "hint"),
                        '#',
                        "",
                        $this->messages->text("hint", "multiples")
                    ), 'hint')
                ), 'width20percent');
            }
        }
        else
        {
            return \HTML\td(\HTML\div('subcategory', "&nbsp;"));
        }
    }
    /**
     * Setter for private property $this->resourceType
     *
     * @param mixed $resourceType
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }
    /**
     * Setter for loading session variables into $this->sessionVars when editing and using AJAX
     */
    public function setSessionVars()
    {
        $this->sessionVars = $this->session->getArray('resourceForm');
    }
    /**
     * We're editing a resource so get resource details and place in $this->sessionVars
     */
    private function getEditSession()
    {
        $badInput = FACTORY_BADINPUT::getInstance();
        if (!array_key_exists('id', $this->vars) || !$this->vars['id'])
        {
            $errors = FACTORY_ERRORS::getInstance();
            $badInput->close($errors->text("inputError", "missing"));
        }
        $this->edit = TRUE;
        $res = FACTORY_RESOURCECOMMON::getInstance();
        $resultset = $res->getResource($this->vars['id']);
        if (!$this->db->numRows($resultset))
        {
            $badInput->close($this->messages->text("resources", "noResult"));
        }
        $row = $this->db->fetchRow($resultset);
        $this->resourceType = $this->sessionVars['resourceType'] = $row['resourceType'];
        $this->sessionVars['resourceTitle'] = $row['resourceTitle'];
        if (array_key_exists('resourceNoSort', $row) && $row['resourceNoSort'])
        {
            $this->sessionVars['resourceNoSort'] = $row['resourceNoSort'];
        }
        if (array_key_exists('resourceSubtitle', $row) && $row['resourceSubtitle'])
        {
            $this->sessionVars['resourceSubtitle'] = $row['resourceSubtitle'];
        }
        if (array_key_exists('resourceShortTitle', $row) && $row['resourceShortTitle'])
        {
            $this->sessionVars['resourceShortTitle'] = $row['resourceShortTitle'];
        }
        if (array_key_exists('resourceTransNoSort', $row) && $row['resourceTransNoSort'])
        {
            $this->sessionVars['resourceTransNoSort'] = $row['resourceTransNoSort'];
        }
        if (array_key_exists('resourcetextUrls', $row) && $row['resourcetextUrls'])
        {
            $tmp = base64_decode($row['resourcetextUrls']);
            $tmp = unserialize($tmp);
            $tmp = array_shift($tmp);
            $this->sessionVars['resourcetextUrl'] = $tmp;
            if (array_key_exists('resourcetextUrlText', $row) && $row['resourcetextUrlText'])
            {
                $tmp = base64_decode($row['resourcetextUrlText']);
                $tmp = unserialize($tmp);
                $tmp = array_shift($tmp);
                $this->sessionVars['resourcetextUrlText'] = $tmp;
            }
        }
        $ids = [];
        foreach ($this->resourceMap->getTables($this->resourceType) as $table)
        {
            foreach ($this->resourceMap->getOptional() as $optional)
            {
                if (!array_key_exists($optional, $this->typeMaps[$this->resourceType]['optional']))
                {
                    continue;
                }
                if (array_key_exists($table, $this->typeMaps[$this->resourceType]['optional'][$optional]))
                {
                    foreach ($this->typeMaps[$this->resourceType]['optional'][$optional][$table] as $key => $value)
                    {
                        $rowKey = $table . $key;
                        if (array_key_exists($rowKey, $row) && $row[$rowKey])
                        {
                            $this->sessionVars[$rowKey] = $row[$rowKey];
                        }
                    }
                }
                if (array_key_exists($table . '*', $this->typeMaps[$this->resourceType]['optional'][$optional]))
                {
                    foreach ($this->typeMaps[$this->resourceType]['optional'][$optional][$table . '*'] as $key => $value)
                    {
                        $rowKey = $table . $key;
                        if (array_key_exists($rowKey, $row) && $row[$rowKey])
                        {
                            $this->sessionVars[$rowKey] = $row[$rowKey];
                        }
                    }
                }
            }
            if (array_key_exists('virtual', $this->typeMaps[$this->resourceType]))
            {
                if (array_key_exists($table, $this->typeMaps[$this->resourceType]['virtual']))
                {
                    foreach ($this->typeMaps[$this->resourceType]['virtual'][$table] as $key => $value)
                    {
                        $rowKey = $table . $key;
                        if (array_key_exists($rowKey, $row) && $row[$rowKey])
                        {
                            $this->sessionVars[$value] = $row[$rowKey];
                            $ids[] = $value;
                        }
                    }
                }
            }
        }
        // abstract, note, isbn, doi
        if (array_key_exists('resourcetextAbstract', $row) && $row['resourcetextAbstract'])
        {
            $this->sessionVars['resourcetextAbstract'] = $row['resourcetextAbstract'];
        }
        if (array_key_exists('resourcetextNote', $row) && $row['resourcetextNote'])
        {
            $this->sessionVars['resourcetextNote'] = $row['resourcetextNote'];
        }
        if (array_key_exists('resourceIsbn', $row) && $row['resourceIsbn'])
        {
            $this->sessionVars['resourceIsbn'] = $row['resourceIsbn'];
        }
        if (array_key_exists('resourceDoi', $row) && $row['resourceDoi'])
        {
            $this->sessionVars['resourceDoi'] = $row['resourceDoi'];
        }
        // creators
        if (array_key_exists('resourcecreator', $this->typeMaps[$this->resourceType]))
        {
            $array = [];
            $this->db->formatConditions(['resourcecreatorResourceId' => $this->vars['id']]);
            $this->db->orderBy(['resourcecreatorRole', 'resourcecreatorOrder'], TRUE, FALSE);
            $resultset = $this->db->select('resource_creator', ['resourcecreatorCreatorId', 'resourcecreatorRole', 'resourcecreatorOrder']);
            while ($row = $this->db->fetchRow($resultset))
            {
                $array[$row['resourcecreatorRole']][] = $row['resourcecreatorCreatorId'];
            }
            foreach ($this->typeMaps[$this->resourceType]['resourcecreator'] as $key => $value)
            {
                foreach ($array as $role => $cArray)
                {
                    foreach ($cArray as $index => $cId)
                    {
                        $this->sessionVars["Creator$role" . "_$index" . '_select'] = $cId;
                    }
                }
            }
        }
        // Get keywords, categories, subcategories, userTags
        $array = [];
        $this->db->formatConditions(['resourcekeywordResourceId' => $this->vars['id']]);
        $this->db->leftJoin('keyword', 'keywordId', 'resourcekeywordKeywordId');
        $resultset = $this->db->select('resource_keyword', 'keywordKeyword');
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = $row['keywordKeyword'];
        }
        if (!empty($array))
        {
            $this->sessionVars['resourcekeywordKeywords'] = implode(',', $array);
        }
        $array = [];
        $this->db->formatConditions(['resourcecategoryCategoryId' => ' IS NOT NULL']);
        $this->db->formatConditions(['resourcecategoryResourceId' => $this->vars['id']]);
        $resultset = $this->db->select('resource_category', 'resourcecategoryCategoryId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = $row['resourcecategoryCategoryId'];
        }
        if (!empty($array))
        {
            $this->sessionVars['resourcecategoryCategories'] = implode(',', $array);
        }
        $array = [];
        $this->db->formatConditions(['resourcecategoryResourceId' => $this->vars['id']]);
        $this->db->formatConditions(['resourcecategorySubcategoryId' => ' IS NOT NULL']);
        $resultset = $this->db->select('resource_category', 'resourcecategorySubcategoryId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = $row['resourcecategorySubcategoryId'];
        }
        if (!empty($array))
        {
            $this->sessionVars['resourcecategorySubcategories'] = implode(',', $array);
        }
        $array = [];
        $this->db->formatConditions(['resourceusertagsResourceId' => $this->vars['id']]);
        $this->db->leftJoin('user_tags', 'usertagsId', 'resourceusertagsTagId');
        $resultset = $this->db->select('resource_user_tags', 'usertagsTag');
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = $row['usertagsTag'];
        }
        if (!empty($array))
        {
            $this->sessionVars['resourceusertagsTagId'] = implode(',', $array);
        }
        // User bibliographies
        $this->db->formatConditions(['userbibliographyUserId' => $this->session->getVar('setup_UserId')]);
        $resultset = $this->db->select('user_bibliography', 'userbibliographyId');
        $array = [];
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = $row['userbibliographyId'];
        }
        if (!empty($array))
        {
            $this->db->formatconditionsOneField($array, 'userbibliographyresourceBibliographyId');
            $this->db->formatConditions(['userbibliographyresourceResourceId' => $this->vars['id']]);
            $resultset = $this->db->select('user_bibliography_resource', 'userbibliographyresourceBibliographyId');
            $array = [];
            while ($row = $this->db->fetchRow($resultset))
            {
                $array[] = $row['userbibliographyresourceBibliographyId'];
            }
            if (!empty($array))
            {
                $this->sessionVars['bibliographies'] = implode(',', $array);
            }
        }
        // Languages
        $array = [];
        $this->db->formatConditions(['resourcelanguageResourceId' => $this->vars['id']]);
        $resultset = $this->db->select('resource_language', 'resourcelanguageLanguageId');
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[] = $row['resourcelanguageLanguageId'];
        }
        if (!empty($array))
        {
            $this->sessionVars['resourcelanguageLanguages'] = implode(',', $array);
        }
        // Remove 'virtualFields' values (see RESOURCEMAP.php)
        foreach ($ids as $value)
        {
            if (array_key_exists('virtualFields', $this->typeMaps[$this->resourceType]) &&
                array_key_exists($value, $this->typeMaps[$this->resourceType]['virtualFields']))
            {
                foreach ($this->typeMaps[$this->resourceType]['virtualFields'][$value] as $removeField)
                {
                    if (array_key_exists($removeField, $this->sessionVars))
                    {
                        unset($this->sessionVars[$removeField]);
                    }
                }
            }
        }
        if (array_key_exists('resourcemiscPeerReviewed', $this->sessionVars) && ($this->sessionVars['resourcemiscPeerReviewed'] == 'N'))
        {
            unset($this->sessionVars['resourcemiscPeerReviewed']);
        }
        $this->session->writeArray($this->sessionVars, 'resourceForm');
    }
    /**
     * Print custom fields if any exist and we're not editing
     *
     * @return string
     */
    private function customCells()
    {
        $array = $small = $large = [];
        $this->db->orderBy('customSize', TRUE, FALSE); // 'L' before 'S'
        $resultset = $this->db->select('custom', ['customSize', 'customLabel', 'customId']);
        while ($row = $this->db->fetchRow($resultset))
        {
            $array[$row['customId']] = [$row['customLabel'], $row['customSize']];
            if ($row['customSize'] == 'L')
            {
                $large[$row['customId']] = \HTML\dbToFormTidy($row['customLabel']);
            }
            else
            {
                $small[$row['customId']] = \HTML\dbToFormTidy($row['customLabel']);
            }
        }
        if (empty($array))
        {
            return;
        }
        $this->sessionVars['customFields'] = serialize($array);
        $pString = \HTML\td(\HTML\h($this->messages->text('resources', 'customFields'), FALSE, 4), $this->tdLabelWidth);

        $tdContent = \HTML\tableStart();
        $tdContent .= \HTML\trStart();

        $count = 3;
        foreach ($small as $id => $label)
        {
            $value = FALSE;
            if ($this->error)
            {
                $value = $this->session->getVar('resourceForm_customId' . $id) ?
                    $this->session->getVar('resourceForm_customId' . $id) : FALSE;
            }
            elseif ($this->edit)
            {
                $this->db->formatConditions(['resourcecustomResourceId' => $this->vars['id']]);
                $this->db->formatConditions(['resourcecustomCustomId' => $id]);
                $row = $this->db->selectFirstRow('resource_custom', 'resourcecustomShort');
                $value = $row['resourcecustomShort'];
            }
            $tdContent .= \HTML\td(\FORM\textInput($label, 'customId' . $id, $value, 30, 255));

            --$count;
            if (!$count)
            {
                $tdContent .= \HTML\trEnd();
                $tdContent .= \HTML\trStart();
                $count = 3;
            }
        }

        while ($count > 0)
        {
            $tdContent .= \HTML\td('&nbsp;');
            --$count;
        }

        $tdContent .= \HTML\trEnd();
        $tdContent .= \HTML\tableEnd();


        $tdContent .= \HTML\tableStart();
        $tdContent .= \HTML\trStart();

        $idArray = [];
        $count = 1;
        foreach ($large as $id => $label)
        {
            $idArray[] = 'customId' . $id;
            $value = FALSE;
            if ($this->error)
            {
                $value = $this->session->getVar('resourceForm_customId' . $id) ?
                    $this->session->getVar('resourceForm_customId' . $id) : FALSE;
            }
            elseif ($this->edit)
            {
                $this->db->formatConditions(['resourcecustomResourceId' => $this->vars['id']]);
                $this->db->formatConditions(['resourcecustomCustomId' => $id]);
                $row = $this->db->selectFirstRow('resource_custom', 'resourcecustomLong');
                $value = $row['resourcecustomLong'];
            }
            $tdContent .= \HTML\td(\FORM\textareaInput($label, 'customId' . $id, \HTML\dbToFormTidy($value), 60, 10));

            --$count;
            if (!$count)
            {
                $tdContent .= \HTML\trEnd();
                $tdContent .= \HTML\trStart();
                $count = 1;
            }
        }

        while ($count > 0)
        {
            $tdContent .= \HTML\td('&nbsp;');
            --$count;
        }

        $tdContent .= \HTML\trEnd();
        $tdContent .= \HTML\tableEnd();


        if (!empty($idArray))
        {
            $pString .= $this->tinymce->loadMetadataTextarea($idArray);
        }
        $pString .= \HTML\td($tdContent, $this->tdContentWidth);

        return \HTML\tableStart('generalTable borderStyleSolid')
            . \HTML\trStart()
            . $pString
            . \HTML\trEnd()
            . \HTML\tableEnd();
    }
    /**
     * Create table cells for resource type
     *
     * @return string
     */
    private function typeTitleCell()
    {
        $tdLabel = $this->messages->text("resources", "type");
        $tdContent = \HTML\tableStart();
        $tdContent .= \HTML\trStart();
        foreach ($this->typeMaps as $type => $null)
        {
            $array[$type] = $this->messages->text("resourceType", $type);
        }
        $jsonArray = [];
        // Amend creators lists depending upon which resource types are chosen
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=initCreators';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'resourceType',
            'targetDiv' => 'creatorsOuter',
        ];
        // Display collections details depending upon which resource types are chosen
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=initCollection';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'resourceType',
            'targetDiv' => 'organizerOuter',
        ];
        // Display series details depending upon which resource types are chosen
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=initSeries';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'resourceType',
            'targetDiv' => 'seriesOuter',
        ];
        // Display conference details depending upon which resource types are chosen
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=initConference';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'resourceType',
            'targetDiv' => 'conferenceOuter',
        ];
        // Display publisher details depending upon which resource types are chosen
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=initPublisher';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'resourceType',
            'targetDiv' => 'publisherOuter',
        ];
        // Display translation details depending upon which resource types are chosen
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=initTranslation';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'resourceType',
            'targetDiv' => 'translationOuter',
        ];
        // Display miscellaneous details depending upon which resource types are chosen
        $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=initMiscellaneous';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'resourceType',
            'targetDiv' => 'miscellaneousOuter',
        ];
        $js = \AJAX\jActionForm('onchange', $jsonArray);
        $tdContent .= \HTML\td(\FORM\selectedBoxValue(FALSE, "resourceType", $array, $this->resourceType, 15, FALSE, $js));
        $title = array_key_exists("resourceTitle", $this->sessionVars) ?
            \HTML\dbToTinyMCE($this->sessionVars['resourceTitle']) : FALSE;
        $title = array_key_exists("resourceNoSort", $this->sessionVars) ?
            \HTML\dbToTinyMCE($this->sessionVars['resourceNoSort']) . " $title" : FALSE . $title;
        $subtitle = array_key_exists("resourceSubtitle", $this->sessionVars) ?
            \HTML\dbToTinyMCE($this->sessionVars['resourceSubtitle']) : FALSE;
        $shortTitle = array_key_exists("resourceShortTitle", $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourceShortTitle']) : FALSE;
        $tdContent .= \HTML\td($this->required() . \FORM\textInput(
            $this->messages->text("resources", "title"),
            "resourceTitle",
            $title,
            75
        ) . BR .
            \FORM\textInput(
                $this->messages->text("resources", "subTitle"),
                "resourceSubtitle",
                $subtitle,
                75
            ) . BR .
            \FORM\textInput(
                $this->messages->text("resources", "shortTitle"),
                "resourceShortTitle",
                $shortTitle,
                35
            ));
        $tdContent .= \HTML\trEnd() . \HTML\tableEnd();
        $div = \HTML\td(\HTML\h($tdLabel, FALSE, 4) .
            \HTML\span($this->messages->text("hint", "capitals"), 'hint'), $this->tdLabelWidth)
            . \HTML\td($tdContent, $this->tdContentWidth);

        return \HTML\div('typeOuter', \HTML\tableStart('generalTable borderStyleSolid') . \HTML\trStart() .
            $div . \HTML\trEnd() . \HTML\tableEnd());
    }
    /**
     * Parse [type]['optional'] in RESOURCEMAP and produce required table and form elements.
     *
     * @param mixed $optionalArraythe array element in [type]['optional'] to process (e.g. 'translation', 'collection' etc.)
     * @param mixed $outerDivthe encapsulating DIV
     * @param mixed $insertBeforearray of inserts
     * @param mixed $insertAfterarray of inserts
     * @param mixed $tdLabel left hand td cell label for table
     *
     * @return string
     */
    private function optionalCellsFormat($optionalArray, $outerDiv, $insertBefore, $insertAfter, $tdLabel)
    {
        if ((($this->resourceType == 'web_site') || ($this->resourceType == 'web_article') || ($this->resourceType == 'database') ||
            ($this->resourceType == 'web_encyclopedia') || ($this->resourceType == 'web_encyclopedia_article')) &&
            ($optionalArray == 'miscellaneous'))
        {
            $date = getdate();
        }
        if (array_key_exists('title', $this->typeMaps[$this->resourceType]['optional'][$optionalArray]))
        {
            $tdLabel = $this->messages->text("resources", $this->typeMaps[$this->resourceType]['optional'][$optionalArray]['title']);
            unset($this->typeMaps[$this->resourceType]['optional'][$optionalArray]['title']);
        }
        else
        {
            $tdLabel = $this->messages->text("resources", $tdLabel);
        }
        if (array_key_exists('titleHint', $this->typeMaps[$this->resourceType]['optional'][$optionalArray]))
        {
            $tdHint = \HTML\p(\HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", $this->typeMaps[$this->resourceType]['optional'][$optionalArray]['titleHint'])
            ), 'hint'));
            unset($this->typeMaps[$this->resourceType]['optional'][$optionalArray]['titleHint']);
        }
        else
        {
            $tdHint = FALSE;
        }
        $contentTable = \HTML\tableStart() . \HTML\trStart();
        $tds = $tdElements = $hints = [];
        foreach ($this->typeMaps[$this->resourceType]['optional'][$optionalArray] as $tableKey => $tableArray)
        {
            $tableKey = str_replace('*', '', $tableKey); // RESOURCEMAP: a '*' is sometimes appended to the table name in order to help with the visual display
            if (($tableKey == 'insertBefore') || ($tableKey == 'insertAfter')
                 || ($tableKey == 'insertTitle'))
            { // should have been dealt with and removed earlier
                continue;
            }
            if (mb_strpos($tableKey, 'hint_') === 0)
            { // found at start
                $hintArray = UTF8::mb_explode('_', $tableKey);
                $hints[$hintArray[1]] = BR . \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", $this->typeMaps[$this->resourceType]['optional'][$optionalArray][$tableKey])
                ), 'hint');

                continue;
            }
            $continue = FALSE;
            foreach ($tableArray as $key => $value)
            {
                if (!is_bool($value) && array_key_exists($value, $this->eds))
                {
                    $required = FALSE;
                    if (array_key_exists('required', $this->typeMaps[$this->resourceType]))
                    {
                        $required = array_search($tableKey . $key, $this->typeMaps[$this->resourceType]['required']) !== FALSE ?
                            $this->required() : FALSE;
                    }
                    $sessionVar = array_key_exists($tableKey . $key, $this->sessionVars) ?
                        \HTML\dbToFormTidy($this->sessionVars[$tableKey . $key]) : FALSE;
                    $hint = array_key_exists($key, $hints) ? $hints[$key] : FALSE;
                    if (array_key_exists($key, $insertBefore))
                    {
                        $tdElements[] = $insertBefore[$key];
                    }
                    if ($this->eds[$value]['type'] == 'textInput')
                    {
                        if (($key == 'TransTitle') && (array_key_exists($tableKey . 'TransNoSort', $this->sessionVars)))
                        {
                            $sessionVar = \HTML\dbToFormTidy($this->sessionVars[$tableKey . 'TransNoSort']) . ' ' . $sessionVar;
                        }
                        if (isset($date) && !$sessionVar && ($key == 'Year2'))
                        {
                            $sessionVar = $date['year'];
                        }
                        $tdElements[] = $required . \FORM\textInput(
                            $this->messages->text("resources", $value),
                            $tableKey . $key,
                            $sessionVar,
                            $this->eds[$value]['width'],
                            $this->eds[$value]['max']
                        ) . $hint;
                    }
                    elseif ($this->eds[$value]['type'] == 'textareaInput')
                    {
                        $tdElements[] = $required . \FORM\textareaInput(
                            $this->messages->text("resources", $value),
                            $tableKey . $key,
                            $sessionVar,
                            $this->eds[$value]['width'],
                            $this->eds[$value]['max']
                        ) . $hint;
                    }
                    elseif ($this->eds[$value]['type'] == 'checkbox')
                    {
                        $tdElements[] = \FORM\checkbox($this->messages->text("resources", $value), $tableKey . $key, $sessionVar) . $hint;
                    }
                    elseif ($this->eds[$value]['type'] == 'date')
                    {
                        $tdElements[] = \FORM\dateInput($this->messages->text("resources", "date"), $value, FALSE);
                    }
                    elseif ($this->eds[$value]['type'] == 'day_selectbox')
                    {
                        if (!isset($days))
                        {
                            $days[] = $this->messages->text("misc", "ignore");
                            $days = array_merge($days, range(1, 31));
                        }
                        if (isset($date) && !$sessionVar)
                        {
                            $sessionVar = $date['mday'];
                        }
                        if ($sessionVar)
                        {
                            $tdElements[] = $required . \FORM\selectedBoxValue(
                                $this->messages->text("resources", $value),
                                $tableKey . $key,
                                $days,
                                $sessionVar,
                                1
                            ) . $hint;
                        }
                        else
                        {
                            $tdElements[] = $required . \FORM\selectFBoxValue(
                                $this->messages->text("resources", $value),
                                $tableKey . $key,
                                $days,
                                1
                            ) . $hint;
                        }
                    }
                    elseif ($this->eds[$value]['type'] == 'month_selectbox')
                    {
                        $constant = FACTORY_CONSTANTS::getInstance();
                        if (!isset($months))
                        {
                            $months[] = $this->messages->text("misc", "ignore");
                            $months = array_merge($months, $constant->monthToLongName());
                        }
                        if (!isset($days))
                        {
                            $days[] = $this->messages->text("misc", "ignore");
                            $days = array_merge($days, range(1, 31));
                        }
                        if (isset($date) && !$sessionVar)
                        {
                            $sessionVar = $date['mon'];
                        }
                        if ($sessionVar)
                        {
                            $tdElements[] = $required . \FORM\selectedBoxValue(
                                $this->messages->text("resources", $value),
                                $tableKey . $key,
                                $months,
                                $sessionVar,
                                1
                            ) . $hint;
                        }
                        else
                        {
                            $tdElements[] = $required . \FORM\selectFBoxValue(
                                $this->messages->text("resources", $value),
                                $tableKey . $key,
                                $months,
                                1
                            ) . $hint;
                        }
                    }
                    if (array_key_exists($key, $insertAfter))
                    {
                        $tdElements[] = $insertAfter[$key];
                    }
                }
                elseif ($key == 'continue')
                {
                    $continue = TRUE;
                }
                elseif (mb_strpos($key, 'break') === 0)
                {
                    $tds[] = $tdElements;
                    $tdElements = [];
                }
            }
            if (!$continue)
            {
                $tds[] = $tdElements;
                $tdElements = [];
            }
        }
        $tdWidth = 'width' . floor(100 / count($tds)) . 'percent';
        foreach ($tds as $td)
        {
            $tdString = implode(BR, $td);
            $contentTable .= \HTML\td($tdString, $tdWidth);
        }
        $contentTable .= \HTML\trEnd() . \HTML\tableEnd();
        $table = \HTML\td(\HTML\h($tdLabel, FALSE, 4) . $tdHint, $this->tdLabelWidth)
            . \HTML\td($contentTable, $this->tdContentWidth);

        return \HTML\div($outerDiv, \HTML\tableStart('generalTable borderStyleSolid') . \HTML\trStart() .
            $table . \HTML\trEnd() . \HTML\tableEnd());
    }
    /**
     * Select box publisher insert for conferences -- fields filled in after AJAX select conference
     *
     * @return array
     */
    private function publisherInsertFill()
    {
        // Get a resourceId to fill in conference details from
        $this->db->formatConditions(['resourcemiscCollection' => $this->collectionFill]);
        $this->db->formatConditions(['resourcemiscField1' => ' IS NOT NULL']);
        $this->db->limit(1, 0); // pick just the first one
        $resultset = $this->db->select('resource_misc', ['resourcemiscId', 'resourcemiscField1']);
        if (!$this->db->numRows($resultset))
        {
            unset($this->sessionVars['publisherId']);

            return $this->publisherInsert();
        }
        $row = $this->db->fetchRow($resultset);
        $publisherId = $row['resourcemiscField1'];
        $resourceId = $row['resourcemiscId'];
        foreach ($this->typeMaps[$this->resourceType]['optional']['publisher'] as $tableKey => $tableArray)
        {
            if (($tableKey == 'insertBefore') || ($tableKey == 'insertAfter') || ($tableKey == 'title')
                 || ($tableKey == 'insertTitle') || ($tableKey == 'publisher') || ($tableKey == 'resourcepage')
                 || (mb_strpos($tableKey, 'hint_') === 0))
            {
                continue;
            }
            if (array_key_exists('break', $tableArray))
            {
                unset($tableArray['break']);
            }
            if (array_key_exists('break2', $tableArray))
            {
                unset($tableArray['break2']);
            }
            if (array_key_exists('continue', $tableArray))
            {
                unset($tableArray['continue']);
            }
            if ($tableKey == 'resourcemisc')
            {
                $table = 'resource_misc';
            }
            elseif ($tableKey == 'resourceyear')
            {
                $table = 'resource_year';
            }
            else
            {
                $table = $tableKey;
            }
            $fieldArray = array_keys($tableArray);
            $fieldArrayDB = $this->db->prependTableToField($tableKey, $fieldArray);
            $this->db->formatConditions([$tableKey . 'Id' => $resourceId]);
            $row = $this->db->selectFirstRow($table, $fieldArrayDB);
            foreach ($fieldArrayDB as $field)
            {
                if ($row[$field])
                {
                    $this->sessionVars[$field] = $row[$field];
                }
                else
                {
                    unset($this->sessionVars[$field]);
                }
            }
        }
        if (($this->resourceType == 'proceedings_article') || ($this->resourceType == 'proceedings'))
        {
            $this->sessionVars['organizerId'] = $publisherId;
        }
        else
        {
            $this->sessionVars['publisherId'] = $publisherId;
        }

        return $this->publisherInsert();
    }
    /**
     * Select box publishers insert for the publishers table
     *
     * @return array
     */
    private function publisherInsert()
    {
        if (array_key_exists($this->resourceType, $this->publisherMap->publisherTypes))
        {
            $tempA = $this->publisher->grabAll($this->publisherMap->publisherTypes[$this->resourceType]);
        }
        else
        {
            $tempA = $this->publisher->grabAll();
        }
        if (!is_array($tempA))
        {
            return [];
        }
        $temp = [];
        foreach ($tempA as $key => $value)
        {
            $temp[$key] = preg_replace("/{(.*)}/Uu", "$1", $value);
        }
        if (!empty($temp))
        {
            if (array_key_exists('insertTitle', $this->typeMaps[$this->resourceType]['optional']['publisher']))
            {
                if ($this->typeMaps[$this->resourceType]['optional']['publisher']['insertTitle'])
                {
                    $title =
                    $this->messages->text("resources", $this->typeMaps[$this->resourceType]['optional']['publisher']['insertTitle']);
                }
            }
            else
            {
                $title = $this->messages->text("resources", "publisher");
            }
            $publishers[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value)
            {
                $publishers[$key] = $value;
            }
            if (($this->resourceType == 'proceedings_article') || ($this->resourceType == 'proceedings'))
            {
                $id = 'organizerId';
            }
            else
            {
                $id = 'publisherId';
            }
            $pId = array_key_exists($id, $this->sessionVars) ?
                \HTML\dbToFormTidy($this->sessionVars[$id]) : FALSE;
            if ($pId)
            {
                return \FORM\selectedBoxValue($title, $id, $publishers, $pId, 1);
            }
            else
            {
                return \FORM\selectFBoxValue($title, $id, $publishers, 1);
            }
        }

        return [];
    }
    /**
     * Select box translation insert for translation -- fields filled in after AJAX select translation
     */
    private function translationInsertFill()
    {
        // Get a resourceId to fill in translation details from
        $array = unserialize(base64_decode($this->translationFill));
        if ($array[0])
        {
            $this->db->formatConditions(['resourceTransNoSort' => $array[0]]); // noSort
        }
        else
        {
            $this->db->formatConditions(['resourceTransNoSort' => ' IS NULL']); // noSort
        }
        $this->db->formatConditions(['resourceTransTitle' => $array[1]]); // title
        if (count($array) > 2)
        {
            $this->db->formatConditions(['resourceTransSubtitle' => $array[2]]); // subTitle
        }
        else
        {
            $this->db->formatConditions(['resourceTransSubtitle' => ' IS NULL']); // subTitle
        }
        $this->db->limit(1, 0); // pick just the first one
        $resourceId = $this->db->selectFirstField('resource', 'resourceId');
        foreach ($this->typeMaps[$this->resourceType]['optional']['translation'] as $tableKey => $tableArray)
        {
            if (($tableKey == 'insertBefore') || ($tableKey == 'insertAfter') || ($tableKey == 'title')
                 || ($tableKey == 'insertTitle') || ($tableKey == 'publisher') || (mb_strpos($tableKey, 'hint_') === 0))
            {
                continue;
            }
            if (array_key_exists('break', $tableArray))
            {
                unset($tableArray['break']);
            }
            if (array_key_exists('continue', $tableArray))
            {
                unset($tableArray['continue']);
            }
            if ($tableKey == 'resourcemisc')
            {
                $table = 'resource_misc';
            }
            elseif ($tableKey == 'resourceyear')
            {
                $table = 'resource_year';
            }
            else
            {
                $table = $tableKey;
            }
            $fieldArray = array_keys($tableArray);
            $fieldArrayDB = $this->db->prependTableToField($tableKey, $fieldArray);
            $this->db->formatConditions([$tableKey . 'Id' => $resourceId]);
            $row = $this->db->selectFirstRow($table, $fieldArrayDB);
            foreach ($fieldArrayDB as $field)
            {
                if ($row[$field])
                {
                    $this->sessionVars[$field] = $row[$field];
                }
                else
                {
                    unset($this->sessionVars[$field]);
                }
            }
        }
        $this->db->formatConditions(['resourcemiscId' => $resourceId]);
        $resultset = $this->db->select('resource_misc', 'resourcemiscField1');
        $row = $this->db->fetchRow($resultset);
        if ($row['resourcemiscField1'])
        {
            $this->sessionVars['transPublisherId'] = $row['resourcemiscField1'];
        }
        else
        {
            unset($this->sessionVars['transPublisherId']);
        }

        return [$this->translationTitleInsert(), $this->translationPubInsert()]; // sets title and publisher select boxes back to IGNORE
    }
    /**
     * Select box titles insert for the translation table
     *
     * @return array
     */
    private function translationTitleInsert()
    {
        $temp = [];
        $this->db->formatConditions(['resourceType' => $this->resourceType]);
        $this->db->formatConditions(['resourceTransTitle' => ' IS NOT NULL']);
        $this->db->orderBy('resourceTransTitle');
        $resultset = $this->db->select('resource', ['resourceTransTitle', 'resourceTransSubtitle', 'resourceTransShortTitle',
            'resourceTransNoSort', ], TRUE);
        while ($row = $this->db->fetchRow($resultset))
        {
            if ($row['resourceTransNoSort'])
            {
                $noSort = \HTML\dbToFormTidy($row['resourceTransNoSort']) . ' ';
            }
            else
            {
                $noSort = '';
            }
            if ($row['resourceTransSubtitle'])
            {
                $temp[] = [[$noSort, $row['resourceTransTitle'], $row['resourceTransSubtitle']],
                    $noSort . \HTML\dbToFormTidy($row['resourceTransTitle']) . ': ' . \HTML\dbToFormTidy($row['resourceTransSubtitle']), ];
            }
            else
            {
                $temp[] = [[$noSort, $row['resourceTransTitle']], $noSort . \HTML\dbToFormTidy($row['resourceTransTitle'])];
            }
        }
        if (!empty($temp))
        {
            if (array_key_exists('insertTitle', $this->typeMaps[$this->resourceType]['optional']['translation']))
            {
                if ($this->typeMaps[$this->resourceType]['optional']['translation']['insertTitle'])
                {
                    $title =
                    $this->messages->text("resources", $this->typeMaps[$this->resourceType]['optional']['translation']['insertTitle']);
                }
            }
            else
            {
                $title = $this->messages->text("resources", "originalTitle");
            }
            // Fill in trans DIV depending upon which trans is chosen
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillTrans&resourceType=' . $this->resourceType;
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'transTitles',
                'targetDiv' => 'translationOuter',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $titles[base64_encode('IGNORE')] = $this->messages->text("misc", "ignore");
            foreach ($temp as $value)
            {
                $titles[base64_encode(serialize($value[0]))] = $value[1];
            }
            $tId = array_key_exists("transTitles", $this->sessionVars) ?
                \HTML\dbToFormTidy($this->sessionVars['transTitles']) : FALSE;
            if ($tId)
            {
                return \FORM\selectedBoxValue($title, 'transTitles', $titles, $tId, 1, FALSE, $js);
            }
            else
            {
                return \FORM\selectFBoxValue($title, 'transTitles', $titles, 1, FALSE, $js);
            }
        }

        return [];
    }
    /**
     * Select box publishers insert for the translation table
     *
     * @return array
     */
    private function translationPubInsert()
    {
        $tempA = $this->publisher->grabAll();
        if (!is_array($tempA))
        {
            return [];
        }
        $temp = [];
        foreach ($tempA as $key => $value)
        {
            $temp[$key] = preg_replace("/{(.*)}/Uu", "$1", $value);
        }
        if (!empty($temp))
        {
            if (array_key_exists('insertTitle', $this->typeMaps[$this->resourceType]['optional']['translation']))
            {
                if ($this->typeMaps[$this->resourceType]['optional']['translation']['insertTitle'])
                {
                    $title =
                    $this->messages->text("resources", $this->typeMaps[$this->resourceType]['optional']['translation']['insertTitle']);
                }
            }
            else
            {
                $title = $this->messages->text("resources", "publisher");
            }
            $publishers[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value)
            {
                $publishers[$key] = $value;
            }
            $pId = array_key_exists("transPublisherId", $this->sessionVars) ?
                \HTML\dbToFormTidy($this->sessionVars['transPublisherId']) : FALSE;
            if ($pId)
            {
                return \FORM\selectedBoxValue($title, 'transPublisherId', $publishers, $pId, 1);
            }
            else
            {
                return \FORM\selectFBoxValue($title, 'transPublisherId', $publishers, 1);
            }
        }

        return [];
    }
    /**
     * Select box series insert for series -- fields filled in after AJAX select series
     *
     * @return string|FALSE
     */
    private function seriesInsertFill()
    {
        // Get a resourceId to fill in series details from
        if (($this->resourceType == 'proceedings') || ($this->resourceType == 'proceedings_article'))
        {
            $this->db->formatConditionsOneField(['proceedings', 'proceedings_article'], 'resourceType');
        }
        elseif (($this->resourceType == 'book') || ($this->resourceType == 'book_article') || ($this->resourceType == 'book_chapter'))
        {
            $this->db->formatConditionsOneField(['book', 'book_article', 'book_chapter'], 'resourceType');
        }
        $this->db->formatConditions(['resourceField1' => base64_decode($this->seriesFill)]);
        $this->db->limit(1, 0); // pick just the first one
        $resourceId = $this->db->selectFirstField('resource', 'resourceId');
        foreach ($this->typeMaps[$this->resourceType]['optional']['series'] as $tableKey => $tableArray)
        {
            if (($tableKey == 'insertBefore') || ($tableKey == 'insertAfter') || ($tableKey == 'title')
                 || ($tableKey == 'insertTitle') || (mb_strpos($tableKey, 'hint_') === 0))
            {
                continue;
            }
            if (array_key_exists('break', $tableArray))
            {
                unset($tableArray['break']);
            }
            if (array_key_exists('continue', $tableArray))
            {
                unset($tableArray['continue']);
            }
            if ($tableKey == 'resourcemisc')
            {
                $table = 'resource_misc';
            }
            elseif ($tableKey == 'resourceyear')
            {
                $table = 'resource_year';
            }
            else
            {
                $table = $tableKey;
            }
            $fieldArray = array_keys($tableArray);
            $fieldArrayDB = $this->db->prependTableToField($tableKey, $fieldArray);
            $this->db->formatConditions([$tableKey . 'Id' => $resourceId]);
            $row = $this->db->selectFirstRow($table, $fieldArrayDB);
            foreach ($fieldArrayDB as $field)
            {
                if ($row[$field])
                {
                    $this->sessionVars[$field] = $row[$field];
                }
                else
                {
                    unset($this->sessionVars[$field]);
                }
            }
        }

        return $this->seriesInsert(); // sets select box back to IGNORE
    }
    /**
     * Select box series insert for series -- initial blank fields
     *
     * @return string|FALSE
     */
    private function seriesInsert()
    {
        if (($this->resourceType == 'proceedings') || ($this->resourceType == 'proceedings_article'))
        {
            $this->db->formatConditionsOneField(['proceedings', 'proceedings_article'], 'resourceType');
        }
        elseif (($this->resourceType == 'book') || ($this->resourceType == 'book_article') || ($this->resourceType == 'book_chapter'))
        {
            $this->db->formatConditionsOneField(['book', 'book_article', 'book_chapter'], 'resourceType');
        }
        $this->db->formatConditions(['resourceField1' => ' IS NOT NULL']);
        $this->db->orderBy('resourceField1');
        $resultset = $this->db->select('resource', 'resourceField1', TRUE);
        while ($row = $this->db->fetchRow($resultset))
        {
            if ($row['resourceField1'])
            {
                $temp[] = \HTML\dbToFormTidy($row['resourceField1']);
            }
        }
        if (isset($temp))
        {
            // Fill in series DIV depending upon which series is chosen
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillSeries&resourceType=' . $this->resourceType;
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'series',
                'targetDiv' => 'seriesOuter',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $series[base64_encode('IGNORE')] = $this->messages->text("misc", "ignore");
            foreach ($temp as $value)
            {
                $series[base64_encode($value)] = $value;
            }
            //			$selected = array_key_exists('series', $this->sessionVars) ? $this->sessionVars['series'] : FALSE;
            //			if($selected)
            //				return \FORM\selectedBoxValue(FALSE, "series", $series, $selected, 1);
            //			else
            return \FORM\selectFBoxValue(FALSE, "series", $series, 1, FALSE, $js);
        }

        return FALSE;
    }
    /**
     * Select box conference insert for conferences -- fields filled in after AJAX select conference
     *
     * @return string|FALSE
     */
    private function conferenceInsertFill()
    {
        // Get a resourceId to fill in conference details from
        $this->db->formatConditions(['resourcemiscCollection' => $this->collectionFill]);
        $this->db->limit(1, 0); // pick just the first one
        $resourceId = $this->db->selectFirstField('resource_misc', 'resourcemiscId');
        foreach ($this->typeMaps[$this->resourceType]['optional']['conference'] as $tableKey => $tableArray)
        {
            if (($tableKey == 'insertBefore') || ($tableKey == 'insertAfter') || ($tableKey == 'title') || ($tableKey == 'titleHint')
                 || ($tableKey == 'insertTitle') || ($tableKey == 'collection') || (mb_strpos($tableKey, 'hint_') === 0))
            {
                continue;
            }
            if (array_key_exists('break', $tableArray))
            {
                unset($tableArray['break']);
            }
            if (array_key_exists('break2', $tableArray))
            {
                unset($tableArray['break2']);
            }
            if (array_key_exists('continue', $tableArray))
            {
                unset($tableArray['continue']);
            }
            if ($tableKey == 'resourcemisc')
            {
                $table = 'resource_misc';
            }
            elseif ($tableKey == 'resourceyear')
            {
                $table = 'resource_year';
            }
            else
            {
                $table = $tableKey;
            }
            $fieldArray = array_keys($tableArray);
            $fieldArrayDB = $this->db->prependTableToField($tableKey, $fieldArray);
            $this->db->formatConditions([$tableKey . 'Id' => $resourceId]);
            $row = $this->db->selectFirstRow($table, $fieldArrayDB);
            foreach ($fieldArrayDB as $field)
            {
                if ($row[$field])
                {
                    $this->sessionVars[$field] = $row[$field];
                }
                else
                {
                    unset($this->sessionVars[$field]);
                }
            }
        }
        $this->sessionVars['conferenceId'] = $this->collectionFill;

        return $this->conferenceInsert();
    }
    /**
     * Select box conference insert for the conference table
     *
     * @return mixed
     */
    private function conferenceInsert()
    {
        if (!array_key_exists('conference', $this->typeMaps[$this->resourceType]['optional']))
        {
            return;
        }
        // check there are collections/conferences
        if ((($this->resourceType == 'proceedings_article') || ($this->resourceType == 'conference_paper') ||
            ($this->resourceType == 'conference_poster') || ($this->resourceType == 'proceedings'))
            && array_key_exists($this->resourceType, $this->collectionMap->collectionTypes))
        {
            $tempA = $this->collection->grabAll($this->collectionMap->collectionTypes[$this->resourceType]);
        }
        else
        {
            $tempA = $this->collection->grabAll();
        }
        if (!is_array($tempA))
        {
            return [];
        }
        $temp = [];
        foreach ($tempA as $key => $value)
        {
            $temp[$key] = preg_replace("/{(.*)}/Uu", "$1", $value);
        }
        if (!empty($temp))
        {
            // Fill in conference DIV depending upon which conference is chosen
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillConference&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'conferenceOuter',
            ];
            // Fill in conference organizer DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillOrganizer&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'organizerOuter',
            ];

            // Fill in publisher DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillPublisher&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'publisherOuter',
            ];
            // Fill in series DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillSeries&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'seriesOuter',
            ];
            // Fill in miscellaneous DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillMiscellaneous&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'miscellaneousOuter',
            ];
            // Fill in translation DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillTrans&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'translationOuter',
            ];
            // Fill in isbn/ID DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillIsbn&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'isbnOuter',
            ];
            // Fill in DOI DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillDoi&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'doiOuter',
            ];
            // Fill in creator DIVs depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillCreators&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => 'conferenceId',
                'targetDiv' => 'creatorsOuter',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            if (array_key_exists('insertTitle', $this->typeMaps[$this->resourceType]['optional']['conference']))
            {
                if ($this->typeMaps[$this->resourceType]['optional']['conference']['insertTitle'])
                {
                    $title =
                    $this->messages->text("resources", $this->typeMaps[$this->resourceType]['optional']['conference']['insertTitle']);
                }
            }
            else
            {
                $title = $this->messages->text("resources", "collection");
            }
            $collections[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value)
            {
                $collections[$key] = $value;
            }
            $selected = array_key_exists('conferenceId', $this->sessionVars) ? $this->sessionVars['conferenceId'] : FALSE;

            return \FORM\selectedBoxValue($title, "conferenceId", $collections, $selected, 1, FALSE, $js);
        }

        return [];
    }
    /**
     * Select box organizer insert for conferences -- fields filled in after AJAX select conference
     */
    private function collectionInsertFill()
    {
        return $this->collectionInsert();
    }
    /**
     * Select box collection insert for the collection table
     *
     * @return mixed
     */
    private function collectionInsert()
    {
        // check there are collections
        $temp = [];
        if ((($this->resourceType == 'proceedings_article') || ($this->resourceType == 'conference_paper') ||
            ($this->resourceType == 'conference_poster') || ($this->resourceType == 'proceedings'))
            && array_key_exists($this->resourceType, $this->publisherMap->publisherTypes))
        {
            $tempA = $this->publisher->grabAll($this->publisherMap->publisherTypes[$this->resourceType]);
        }
        elseif (($this->resourceType == 'proceedings_article') || ($this->resourceType == 'conference_paper') ||
            ($this->resourceType == 'conference_poster') || ($this->resourceType == 'proceedings'))
        {
            $tempA = $this->publisher->grabAll();
        }
        elseif (array_key_exists($this->resourceType, $this->collectionMap->collectionTypes))
        {
            $tempA = $this->collection->grabAll($this->collectionMap->collectionTypes[$this->resourceType]);
        }
        else
        {
            $tempA = $this->collection->grabAll();
        }
        if (!is_array($tempA))
        {
            return [];
        }
        foreach ($tempA as $key => $value)
        {
            $temp[$key] = preg_replace("/{(.*)}/Uu", "$1", $value);
        }
        if (!empty($temp))
        {
            if (array_key_exists('insertTitle', $this->typeMaps[$this->resourceType]['optional']['collection']))
            {
                if ($this->typeMaps[$this->resourceType]['optional']['collection']['insertTitle'])
                {
                    $title =
                    $this->messages->text("resources", $this->typeMaps[$this->resourceType]['optional']['collection']['insertTitle']);
                }
            }
            else
            {
                $title = $this->messages->text("resources", "collection");
            }
            $collections[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value)
            {
                $collections[$key] = $value;
            }
            if (($this->resourceType == 'conference_paper') || ($this->resourceType == 'conference_poster'))
            {
                $id = 'publisherId';
            }
            elseif (($this->resourceType == 'proceedings_article') || ($this->resourceType == 'proceedings'))
            {
                $id = 'publisherId';
            }
            else
            {
                $id = 'collectionId';
            }
            // Fill in publisher DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillPublisher&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => "$id",
                'targetDiv' => 'publisherOuter',
            ];
            // Fill in series DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillSeries&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => "$id",
                'targetDiv' => 'seriesOuter',
            ];
            // Fill in miscellaneous DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillMiscellaneous&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => "$id",
                'targetDiv' => 'miscellaneousOuter',
            ];
            // Fill in translation DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillTrans&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => "$id",
                'targetDiv' => 'translationOuter',
            ];
            // Fill in isbn/ID DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillIsbn&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => "$id",
                'targetDiv' => 'isbnOuter',
            ];
            // Fill in DOI DIV depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillDoi&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => "$id",
                'targetDiv' => 'doiOuter',
            ];
            // Fill in creator DIVs depending upon which collection is chosen and type of resource
            $jScript = 'index.php?action=resource_RESOURCEFORMAJAX_CORE&method=fillCreators&resourceType=' . $this->resourceType .
                '&fromCollection=1';
            $jsonArray[] = [
                'startFunction' => 'triggerFromSelect',
                'script' => "$jScript",
                'triggerField' => "$id",
                'targetDiv' => 'creatorsOuter',
            ];
            $js = \AJAX\jActionForm('onchange', $jsonArray);
            $selected = array_key_exists($id, $this->sessionVars) ? $this->sessionVars[$id] : FALSE;
            if ($selected)
            {
                return \FORM\selectedBoxValue($title, $id, $collections, $selected, 1, FALSE, $js);
            }
            else
            {
                return \FORM\selectFBoxValue($title, $id, $collections, 1, FALSE, $js);
            }
        }

        return [];
    }
    /**
     * Abstract, notes, categories, subcategories, keywords, user bibliographies, URL, ISBN, DOI etc
     *
     * @return string
     */
    private function otherCell()
    {
        $pString = \HTML\td(\HTML\h($this->messages->text('resources', 'commonDetails'), FALSE, 4) .
            \HTML\span($this->messages->text("hint", "keywordsUserTags"), 'hint'), $this->tdLabelWidth);
        $tdContent1 = \HTML\tableStart() . \HTML\trStart();
        $text = array_key_exists('resourcetextAbstract', $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourcetextAbstract']) : FALSE;
        $tdContent1 .= \HTML\td(\FORM\textareaInput(
            $this->messages->text("resources", "abstract"),
            "resourcetextAbstract",
            $text,
            60,
            10
        ));
        $tdContent1 .= \HTML\trEnd() . \HTML\trStart();
        $text = array_key_exists('resourcetextNote', $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourcetextNote']) : FALSE;
        $tdContent1 .= \HTML\td(\FORM\textareaInput(
            $this->messages->text("resources", "note"),
            "resourcetextNote",
            $text,
            60,
            10
        ));
        $tdContent1 .= \HTML\trEnd() . \HTML\tableEnd();
        $tdContent1 .= BR . '&nbsp;' . BR;
        $tdContent2 = \HTML\tableStart() . \HTML\trStart();
        $text = array_key_exists('resourcetextUrl', $this->sessionVars) ? $this->sessionVars['resourcetextUrl'] : 'http://';
        $urlContent = \FORM\textInput(
            $this->messages->text("resources", "url"),
            "resourcetextUrl",
            $text,
            50,
            255
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "url")
        ), 'hint');
        $text = array_key_exists('resourcetextUrlText', $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourcetextUrlText']) : FALSE;
        $urlContent .= BR . '&nbsp;' . BR;
        $urlContent .= \FORM\textInput(
            $this->messages->text("resources", "urlLabel"),
            "resourcetextUrlText",
            $text,
            50,
            255
        );
        $tdContent2 .= \HTML\td($urlContent, 'width50percent');
        $text = array_key_exists('resourceIsbn', $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourceIsbn']) : FALSE;
        $tdContent2 .= \HTML\td($this->divIsbn(), 'width25percent');
        $text = array_key_exists('resourceDoi', $this->sessionVars) ?
            \HTML\dbToFormTidy($this->sessionVars['resourceDoi']) : '';
        $tdContent2 .= \HTML\td($this->divDoi(), 'width25percent');
        $tdContent2 .= \HTML\trEnd() . \HTML\tableEnd();
        $tdContent2 .= BR . '&nbsp;' . BR;
        $tdContent3 = \HTML\tableStart('borderSpacingSmall') . \HTML\trStart();
        // categories
        $categories = $this->category->grabAll();
        $selectedCategories = [];
        if (array_key_exists('resourcecategoryCategories', $this->sessionVars))
        {
            $selected = UTF8::mb_explode(',', $this->sessionVars['resourcecategoryCategories']);
            foreach ($selected as $key)
            {
                $selectedCategories[$key] = $categories[$key];
                unset($categories[$key]);
            }
        }
        $td = \FORM\selectFBoxValueMultiple(
            $this->messages->text('resources', 'categories'),
            "availableCategory",
            $categories,
            5
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint');
        list($toBottomImage, $toTopImage) = $this->category->transferArrowsCatUpDown();
        $td .= \HTML\p($toBottomImage . '&nbsp;&nbsp;' . $toTopImage);
        $td .= \HTML\p(\FORM\selectFBoxValueMultiple(
            $this->messages->text('search', 'category'),
            "resourcecategoryCategories",
            $selectedCategories,
            5
        ) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "multiples")
        ), 'hint'));
        $tdContent3 .= \HTML\td($td, 'width33percent');
        $temp = $this->category->grabSubAll(TRUE);
        $tdContent3 .= $this->subcategoryBox($temp);

        // Resource languages
        $this->db->orderBy('languageLanguage');
        $resultset = $this->db->select('language', ['languageId', 'languageLanguage']);
        if ($this->db->numRows($resultset))
        {
            $this->languages[0] = $this->messages->text("misc", "ignore");
            while ($row = $this->db->fetchRow($resultset))
            {
                $this->languages[$row['languageId']] = \HTML\dbToFormtidy($row['languageLanguage']);
            }
            if (array_key_exists('resourcelanguageLanguages', $this->sessionVars))
            {
                $selectedLanguages = UTF8::mb_explode(',', $this->sessionVars['resourcelanguageLanguages']);
                $tdContent3 .= \HTML\td(\FORM\selectedBoxValueMultiple($this->messages->text(
                    'resources',
                    'languages'
                ), 'language', $this->languages, $selectedLanguages, 5) . BR . \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "multiples")
                ), 'hint'), 'width33percent');
            }
            else
            {
                $tdContent3 .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text(
                    'resources',
                    'languages'
                ), 'language', $this->languages, 5) . BR . \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "multiples")
                ), 'hint'), 'width33percent');
            }
        }
        $tdContent3 .= \HTML\trEnd() . \HTML\tableEnd();
        $tdContent3 .= BR . '&nbsp;' . BR;


        $tdContent4 = \HTML\tableStart();
        $tdContent4 .= \HTML\trStart();

        // keywords
        $text = FALSE;
        $keywords = $this->keyword->grabAll();
        if (!empty($keywords))
        {
            // If preferences reduce long keywords, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($keywords as $key => $value)
            {
                $key = $key . '_' . base64_encode($value);
                $keywordArray[$key] = html_entity_decode($value);
            }
            $jsonArray = [];
            $jsonArray[] = [
                'startFunction' => 'transferKeyword',
            ];
            $toBottom = \AJAX\jActionIcon('toBottom', 'onclick', $jsonArray);
            $text = array_key_exists('resourcekeywordKeywords', $this->sessionVars) ?
                stripslashes($this->sessionVars['resourcekeywordKeywords']) : FALSE;
            $keywordText = \HTML\p(\FORM\textareaInputmceNoEditor(FALSE, "keywordList", $text, 40, 2) . BR .
                    \HTML\span(\HTML\aBrowse(
                        'green',
                        '',
                        $this->messages->text("hint", "hint"),
                        '#',
                        "",
                        $this->messages->text("hint", "keywordList")
                    ), 'hint'));
            $tdContent4 .= \HTML\td(\FORM\selectFBoxValueMultiple(
                $this->messages->text('resources', 'keywords'),
                'resourcekeywordKeywords',
                $keywordArray,
                5
            ) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint') .
            BR . $toBottom . $keywordText, 'width33percent');
        }
        else
        {
            $keywordText = \FORM\textareaInputmceNoEditor($this->messages->text('resources', 'keywords'), "keywordList", $text, 40, 2) .
                BR . \HTML\span(\HTML\aBrowse(
                    'green',
                    '',
                    $this->messages->text("hint", "hint"),
                    '#',
                    "",
                    $this->messages->text("hint", "multiples")
                ), 'hint');
            $tdContent4 .= \HTML\td($keywordText, 'width33percent');
        }
        // user tags
        $jsonArray = [];
        $jsonArray[] = [
            'startFunction' => 'transferUserTag',
        ];
        $toBottom = \AJAX\jActionIcon('toBottom', 'onclick', $jsonArray);
        $text = array_key_exists('resourceusertagsTagId', $this->sessionVars) ? stripslashes($this->sessionVars['resourceusertagsTagId']) : FALSE;
        $userTagText = \HTML\p(\FORM\textareaInputmceNoEditor(FALSE, "userTagList", $text, 40, 2) . BR . \HTML\span(\HTML\aBrowse(
            'green',
            '',
            $this->messages->text("hint", "hint"),
            '#',
            "",
            $this->messages->text("hint", "keywordList")
        ), 'hint'));
        $userTags = $this->userTagObj->grabAll();
        if (!empty($userTags))
        {
            // If preferences reduce long userTags, we want to transfer the original rather than the condensed version.
            // Store the base64-encoded value for retrieval in the javascript.
            foreach ($userTags as $key => $value)
            {
                $key = $key . '_' . base64_encode($value);
                $userTagArray[$key] = html_entity_decode($value);
            }
            $tdContent4 .= \HTML\td(\FORM\selectFBoxValueMultiple(
                $this->messages->text('resources', 'userTags'),
                'resourceusertagsTagId',
                $userTagArray,
                5
            ) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint') . BR .
                $toBottom . $userTagText, 'width33percent');
        }

        // bibliographies
        $bibs[0] = $this->messages->text("misc", "ignore");
        // Get this user's bibliographies
        if ($this->session->getVar('mywikindx_Bibliographies'))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Bibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsU[$key] = \HTML\dbToFormTidy($value);
            }
        }
        // Get this user's user group bibliographies
        if ($this->session->getVar('mywikindx_Groupbibliographies'))
        {
            $bibsRaw = unserialize($this->session->getVar("mywikindx_Groupbibliographies"));
            foreach ($bibsRaw as $key => $value)
            {
                $bibsUG[$key] = \HTML\dbToFormTidy($value);
            }
        }
        $bibsU = $this->commonBib->getUserBibs();
        if (!empty($bibsU))
        {
            foreach ($bibsU as $key => $value)
            {
                $bibs[$key] = $value;
            }
        }
        $bibsUG = $this->commonBib->getGroupBibs();
        if (!empty($bibsUG))
        {
            foreach ($bibsUG as $key => $value)
            {
                $bibs[$key] = $value;
            }
        }
        if (array_key_exists('bibliographies', $this->sessionVars) && (count($bibs) > 1))
        {
            $selected = UTF8::mb_explode(',', $this->sessionVars['bibliographies']);
            $tdContent4 .= \HTML\td(\FORM\selectedBoxValueMultiple($this->messages->text(
                'resources',
                'addNewResourceToBib'
            ), 'bibliographies', $bibs, $selected, 5) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint'), 'width33percent');
        }
        elseif (count($bibs) > 1)
        {
            $tdContent4 .= \HTML\td(\FORM\selectFBoxValueMultiple($this->messages->text(
                'resources',
                'addNewResourceToBib'
            ), 'bibliographies', $bibs, 5) . BR . \HTML\span(\HTML\aBrowse(
                'green',
                '',
                $this->messages->text("hint", "hint"),
                '#',
                "",
                $this->messages->text("hint", "multiples")
            ), 'hint'), 'width33percent');
        }
        $tdContent4 .= \HTML\trEnd();
        $tdContent4 .= \HTML\tableEnd();


        $pString .= \HTML\td($tdContent1 . $tdContent2 . $tdContent3 . $tdContent4, $this->tdContentWidth);

        return \HTML\div(
            'miscOuter',
            \HTML\tableStart('generalTable borderStyleSolid')
            . \HTML\trStart()
            . $pString
            . \HTML\trEnd()
            . \HTML\tableEnd()
        );
    }
    /**
     * Make the labels for the creator fields
     *
     * @return string
     */
    private function makeCreatorLabel()
    {
        $label = \HTML\trStart();
        $label .= \HTML\td($this->messages->text("resources", "firstname"));
        $label .= \HTML\td($this->messages->text("resources", "initials") .
             BR . \HTML\span(\HTML\aBrowse(
                 'green',
                 '',
                 $this->messages->text("hint", "hint"),
                 '#',
                 "",
                 $this->messages->text("hint", "initials")
             ), 'hint'));
        $label .= \HTML\td($this->messages->text("resources", "prefix"));
        $label .= \HTML\td($this->messages->text("resources", "surname"));
        $label .= \HTML\td('&nbsp;');
        $label .= \HTML\trEnd();

        return $label;
    }
    /**
     * Write session variables for creators to session array 'resourceForm'
     *
     * @param array $array array of session names and values
     *
     * @return array Array of session names and values.
     */
    private function writeSessionCreators($array)
    {
        $temp = $this->session->getArray('resourceForm');
        if (!empty($array['creatorFields']))
        {
            // $array['creatorFields'] does not store fields set to IGNORE or blank so, if creator fields don't exist, remove them from the session.
            foreach ($temp as $key => $value)
            {
                if (!array_key_exists($key, $array['creatorFields']) && (mb_strpos($key, 'Creator') === 0))
                {
                    $this->session->delVar("resourceForm_$key");
                }
            }
            $this->session->writeArray($array['creatorFields'], 'resourceForm');
        }
        // else remove all creator fields
        else
        {
            foreach ($temp as $key => $value)
            {
                if (mb_strpos($key, 'Creator') === 0)
                {
                    $this->session->delVar("resourceForm_$key");
                }
            }
        }
        $this->sessionVars = $this->session->getArray('resourceForm');
    }
    /**
     * Required indicator as HTML element
     *
     * @return string
     */
    private function required()
    {
        return \HTML\span('*', 'required');
    }
    /**
     * Create form element definitions and store in $this->eds array -- form element type and size(s).
     * This is used for RESOURCEMAP's $type['optional'] arrays.  e.g., if RESOURCEMAP has:
     *
     *		$this->book['optional']['transPublisher'] =
     *			array(
     *				'resource' => array(
     *									'TransTitle'	=>	'title',
     *									'TransSubtitle'	=>	'subTitle',
     *									'TransShortTitle' => 'shortTitle',
     *								),
     *				);
     *
     * 'title', 'subTitle' and 'shortTitle' would each be $element below and large textInput.
     */
    private function loadElementDefinitions()
    {
        $ti_width_large = 255;
        $ti_width_medLarge = 140;
        $ti_width_med = 75;
        $ti_width_medSmall = 35;
        $ti_width_small = 10;
        $ti_max_large = 255;
        $ti_max_medLarge = 255;
        $ti_max_med = 255;
        $ti_max_medSmall = 255;
        $ti_max_small = 255;
        $ta_width = 60;
        $ta_height = 10;
        $ti_large = ['type' => 'textInput', 'width' => $ti_width_large, 'max' => $ti_max_large];
        $ti_medLarge = ['type' => 'textInput', 'width' => $ti_width_medLarge, 'max' => $ti_max_medLarge];
        $ti_med = ['type' => 'textInput', 'width' => $ti_width_med, 'max' => $ti_max_med];
        $ti_medSmall = ['type' => 'textInput', 'width' => $ti_width_medSmall, 'max' => $ti_max_medSmall];
        $ti_small = ['type' => 'textInput', 'width' => $ti_width_small, 'max' => $ti_max_small];
        $ta = ['type' => 'textareaInput', 'width' => $ta_width, 'height' => $ta_height];
        $cb = ['type' => 'checkbox'];
        $di = ['type' => 'date'];
        $day_sb = ['type' => 'day_selectbox'];
        $month_sb = ['type' => 'month_selectbox'];
        // textInput large
        foreach ([] as $element)
        {
            $this->eds[$element] = $ti_large;
        }
        // textInput medium large
        foreach ([] as $element)
        {
            $this->eds[$element] = $ti_medLarge;
        }
        // textInput medium
        foreach (['title', 'subTitle', 'seriesTitle',] as $element)
        {
            $this->eds[$element] = $ti_med;
        }
        // textInput medium small
        foreach (['shortTitle', 'section', 'city', 'publisherName', 'publisherLocation',
            'institutionName', 'institutionLocation', 'department', 'distributor', 'country', 'channel',
            'channelLocation', 'typeOfSoftware', 'medium', 'court', 'reporter', 'reporterVolume', 'code',
            'codeVolume', 'legislativeLocation', 'section', 'volume', 'conferenceOrganiser', 'conferenceLocation',
            'hearing', 'legislativeBody', 'committee', 'nameOfFile', 'publishedSource', 'patentType', 'intPatentTitle',
            'intPatentClassification', 'legalStatus', 'typeOfCommunication', 'typeOfWork', 'recordLabel', ] as $element)
        {
            $this->eds[$element] = $ti_medSmall;
        }
        // textInput small
        foreach (['edition', 'publicationYear', 'reprintYear', 'volumeYear', 'pageStart', 'pageEnd',
            'seriesNumber', 'numPages', 'bookVolumeNumber', 'numberOfVolumes', 'journalVolumeNumber',
            'journalIssueNumber', 'startPublicationYear', 'endPublicationYear', 'year', 'revisionYear', 'thesisYear',
            'minutes', 'hours', 'proceedingsVolumeNumber', 'thesis', 'thesisLabel', 'version', 'caseYear',
            'session', 'sessionYear', 'billNumber', 'startYear', 'endYear', 'ruleType', 'ruleNumber',
            'issueNumber', 'typeOfReport', 'documentNumber', 'typeOfArticle', 'typeOfManuscript', 'manuscriptNumber',
            'typeOfMap', 'imageProgram', 'imageType', 'imageSize', 'number', 'publicLawNumber', 'codeNumber',
            'applicationNumber', 'patentVersionNumber', 'intPatentNumber', 'patentNumber',] as $element)
        {
            $this->eds[$element] = $ti_small;
        }
        // textarea input
        foreach ([] as $element)
        {
            $this->eds[$element] = $ta;
        }
        // checkbox input
        foreach (['peerReviewed'] as $element)
        {
            $this->eds[$element] = $cb;
        }
        // date input
        foreach (['publicationDate', 'startDate', 'endDate', 'accessDate'] as $element)
        {
            $this->eds[$element] = $di;
        }
        // day selectbox
        foreach (['day', 'startDay', 'endDay', 'publicationDay'] as $element)
        {
            $this->eds[$element] = $day_sb;
        }
        // month selectbox
        foreach (['month', 'startMonth', 'endMonth', 'publicationMonth'] as $element)
        {
            $this->eds[$element] = $month_sb;
        }
    }
}
