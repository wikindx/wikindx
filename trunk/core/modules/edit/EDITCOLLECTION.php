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
 * EDITCOLLECTION class
 */
class EDITCOLLECTION
{
    private $db;
    private $vars;
    private $errors;
    private $messages;
    private $success;
    private $session;
    private $sessionVars = [];
    private $collection;
    private $gatekeep;
    private $badInput;
    private $map;
    private $defaultMap;
    private $creatorsArray;
    private $defaults = [];

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->success = FACTORY_SUCCESS::getInstance();
        $this->session = FACTORY_SESSION::getInstance();


        $this->collection = FACTORY_COLLECTION::getInstance();
        $this->gatekeep = FACTORY_GATEKEEP::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
    }
    /**
     * check we are allowed to edit and load collection select box
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('collection'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" . $this->messages->text("resources", "collection") . ")"));
        $this->session->clearArray('edit');
        $this->db->groupBy('collectionType');
        $recordset = $this->db->select('collection', 'collectionType');
        if (!$this->db->numRows($recordset)) {
            $pString = \HTML\p("&nbsp;") . $this->messages->text("misc", "noCollections");
            GLOBALS::addTplVar('content', $pString);
        }
        // Add 'ALL' to array
        $collections[0] = $this->messages->text("collection", 'all');
        while ($row = $this->db->fetchRow($recordset)) {
            if (!$row['collectionType']) {
                continue;
            }
            $collections[$row['collectionType']] = $this->messages->text("collection", $row['collectionType']);
        }
        $pString = $message;
        $pString .= \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        $td = \FORM\formHeader('edit_EDITCOLLECTION_CORE');
        $td .= \FORM\hidden("method", "editChooseCollection");
        $td .= \FORM\selectFBoxValue(FALSE, "edit_collectionType", $collections, 10);
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * check we are allowed to edit and load appropriate method
     */
    public function editChooseCollection()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('collection'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" .
            $this->messages->text("resources", "collection") . ")"));
        $collectionType = $this->vars['edit_collectionType'];
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "collection", "COLLECTIONMAP.php"]));
        $this->map = new COLLECTIONMAP();
        if ($collectionType) {
            $this->db->formatConditions(['collectionType' => $collectionType]);
        }
        $this->db->formatConditions($this->db->formatFields('collectionId'), ' IS NOT NULL');
        $this->db->leftJoin('collection', 'collectionId', 'resourcemiscCollection');
        $this->db->groupBy('collectionId');
        $this->db->orderBy('collectionTitle');
        $recordset = $this->db->selectCounts(
            'resource_misc',
            'collectionId',
            ['resourcemiscCollection', 'collectionTitle', 'collectionTitleShort']
        );
        while ($row = $this->db->fetchRow($recordset)) {
            $short = $row['collectionTitleShort'] ?
                " [" . $row['collectionTitleShort'] . ']' : FALSE;
            $title = $row['collectionTitle'] . $short;
            $collections[$row['collectionId']] = preg_replace("/{(.*)}/Uu", "$1", \HTML\dbToFormTidy($title, TRUE));
        }
        $pString = \HTML\tableStart('left');
        $pString .= \HTML\trStart();
        $td = \FORM\formHeader('edit_EDITCOLLECTION_CORE');
        $td .= \FORM\hidden("method", "editDisplayCollection");
        $td .= \FORM\hidden("title", $title);
        $td .= \FORM\hidden("shortTitle", $short);
        $td .= \FORM\selectFBoxValue(FALSE, "edit_collectionId", $collections, 20);
        $td .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")));
        $td .= \FORM\formEnd();
        $pString .= \HTML\td($td);
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Display interface to edit collection
     *
     * @param false|string $message
     */
    public function editDisplayCollection($message = FALSE)
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "help", "HELPMESSAGES.php"]));
        $help = new HELPMESSAGES();
        GLOBALS::setTplVar('help', $help->createLink('collection'));
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" .
            $this->messages->text("resources", "collection") . ")"));
        $this->session->setVar("editLock", FALSE);
        if (!array_key_exists('edit_collectionId', $this->vars) || !$this->vars['edit_collectionId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('title', $this->vars) || !$this->vars['title']) { // coming back here after mis-edit without title
            $this->db->formatConditions(['collectionId' => $this->vars['edit_collectionId']]);
            $recordset = $this->db->select('collection', 'collectionTitle');
            $row = $this->db->fetchRow($recordset);
            $title = $row['collectionTitle'];
        }
        if (array_key_exists('shortTitle', $this->vars) && $this->vars['shortTitle']) {
            $short = $this->vars['shortTitle'];
        } else {
            $short = FALSE;
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "collection", "COLLECTIONDEFAULTMAP.php"]));
        $this->defaultMap = new COLLECTIONDEFAULTMAP();
        $tinymce = FACTORY_LOADTINYMCE::getInstance();
        $pString = $message;
        $pString .= \FORM\formHeader('edit_EDITCOLLECTION_CORE');
        $pString .= \FORM\hidden("method", "edit");
        $pString .= \FORM\hidden("edit_collectionId", $this->vars['edit_collectionId']);
        if (empty($this->sessionVars)) {
            $this->db->formatConditions(['collectionId' => $this->vars['edit_collectionId']]);
            $recordset = $this->db->select('collection', ['collectionTitle', 'collectionTitleShort', 'collectionType', 'collectionDefault']);
            $row = $this->db->fetchRow($recordset);
            if ($row['collectionDefault']) {
                $this->defaults = unserialize(base64_decode($row['collectionDefault']));
            }
            $title = \HTML\dbToTinyMCE($row['collectionTitle']);
            $titleShort = \HTML\dbToFormTidy($row['collectionTitleShort']);
            $pString .= \FORM\hidden("edit_collectionType", $row['collectionType']);
            $collectionType = $row['collectionType'];
        } else { // after a mis-edit
            $this->defaults = $this->sessionVars;
            $title = $this->defaults['collectionTitle'];
            $titleShort = $this->defaults['collectionTitleShort'];
            $pString .= \FORM\hidden("edit_collectionType", $this->defaults['collectionType']);
            $collectionType = $this->defaults['collectionType'];
        }
        $pString .= \HTML\tableStart('generalTable borderStyleSolid');
        $pString .= \HTML\trStart();
        $td = $tinymce->loadBasicTextinput(['edit_collectionTitle'], 600);
        $td .= \HTML\td(\FORM\textAreaInput(
            $this->messages->text('resources', 'collection') . ' ' . \HTML\span('*', 'required'),
            'edit_collectionTitle',
            $title,
            60,
            1
        ));
        $td .= \HTML\td(\FORM\textInput(
            $this->messages->text('resources', 'collectionShort'),
            'edit_collectionTitleShort',
            $titleShort,
            30
        ));
        $pString .= $td;
        $pString .= \HTML\trEnd();
        $pString .= \HTML\tableEnd();
        $pString = \HTML\p($pString);
        if ($collectionType == 'book') {
            $pString .= $this->bookDefaults();
        } elseif ($collectionType == 'journal') {
            $pString .= $this->journalDefaults();
        } elseif ($collectionType == 'proceedings') {
            $pString .= $this->proceedingsDefaults();
        } elseif ($collectionType == 'newspaper') {
            $pString .= $this->newspaperDefaults();
        } elseif ($collectionType == 'magazine') {
            $pString .= $this->magazineDefaults();
        } elseif ($collectionType == 'web') {
            $pString .= $this->webDefaults();
        } elseif ($collectionType == 'thesis') {
            $pString .= $this->thesisDefaults();
        } elseif ($collectionType == 'music') {
            $pString .= $this->musicDefaults();
        } elseif ($collectionType == 'manuscript') {
            $pString .= $this->manuscriptDefaults();
        } elseif ($collectionType == 'miscellaneous') {
            $pString .= $this->miscellaneousDefaults();
        }
        $pString .= \FORM\formSubmit($this->messages->text("submit", "Edit")) . \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
        // Load at end because .js initialization needs various DIVs to be in the page before they are made invisible
        \AJAX\loadJavascript(WIKINDX_URL_BASE . '/core/modules/resource/resourceForm.js?ver=' . WIKINDX_PUBLIC_VERSION);
    }
    /**
     * Add or remove a creator field (AJAX)
     *
     * @param mixed $type
     * @param mixed $addRemove
     * @param mixed $inputArray
     *
     * @return false|string
     */
    public function doAddRemoveCreator($type, $addRemove, $inputArray)
    {
        $this->writeSessionCreators($inputArray);
        $this->getCreators();
        $fields = $label = '';
        for ($index = 0; $index < $inputArray['index']; $index++) {
            if ($index == 0) {
                $label = $this->makeCreatorName();
            }
            $entry = $type . '_' . $index . '_firstname';
            $text = array_key_exists($entry, $inputArray['creatorFields']) ? $inputArray['creatorFields'][$entry] : FALSE;
            $fields .= \HTML\trStart();
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 30, 255));
            $entry = $type . '_' . $index . '_initials';
            $text = array_key_exists($entry, $inputArray['creatorFields']) ? $inputArray['creatorFields'][$entry] : FALSE;
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 6, 255));
            $entry = $type . '_' . $index . '_prefix';
            $text = array_key_exists($entry, $inputArray['creatorFields']) ? $inputArray['creatorFields'][$entry] : FALSE;
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 11, 255));
            $entry = $type . '_' . $index . '_surname';
            $text = array_key_exists($entry, $inputArray['creatorFields']) ? $inputArray['creatorFields'][$entry] : FALSE;
            $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, \HTML\dbToFormTidy($text), 30, 255));
            $entry = $type . '_' . $index . '_select';
            $selected = array_key_exists($entry, $inputArray['creatorFields']) ? $inputArray['creatorFields'][$entry] : FALSE;
            if ($selected) {
                $fields .= \HTML\td(\FORM\selectedBoxValue(FALSE, $entry, $this->creatorsArray, $selected, 1));
            } else {
                $fields .= \HTML\td(\FORM\selectFBoxValue(FALSE, $entry, $this->creatorsArray, 1));
            }
            $fields .= \HTML\trEnd();
        }
        if (!$label) {
            return FALSE;
        } else {
            return \HTML\tableStart() . $label . $fields . \HTML\tableEnd();
        }
    }
    /**
     * write to the database
     */
    public function edit()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        $this->writeVarsToSession();
        if ($this->session->getVar("editLock")) {
            $this->badInput->close($this->errors->text("done", "collection"), $this, 'init');
        }
        if (!array_key_exists('edit_collectionId', $this->vars) || !$this->vars['edit_collectionId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('edit_collectionType', $this->vars)) { // can be NULL
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $title = array_key_exists('edit_collectionTitle', $this->vars) ? UTF8::mb_trim($this->vars['edit_collectionTitle']) : FALSE;
        if (!$title) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'editDisplayCollection');
        }
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "collection", "COLLECTIONDEFAULTMAP.php"]));
        $this->defaultMap = new COLLECTIONDEFAULTMAP();
        $this->db->formatConditions(['collectionId' => $this->vars['edit_collectionId']]);
        $recordset = $this->db->select('collection', ['collectionType', 'collectionDefault']);
        $row = $this->db->fetchRow($recordset);
        if ($row['collectionDefault']) {
            $this->defaults = unserialize(base64_decode($row['collectionDefault']));
        }
        $updateArray = $temp = $miscArray = $yearArray = $resourceArray = [];
        // Deal with creators
        $temp['creators'] = $this->editCreators();
        $titleShort = array_key_exists('edit_collectionTitleShort', $this->vars) ?
            UTF8::mb_trim($this->vars['edit_collectionTitleShort']) : FALSE;
        if ($collectionExistId = $this->collection->checkExists($this->vars['edit_collectionId'], $title, $titleShort, $this->vars['edit_collectionType'])) {
            if ($collectionExistId != $this->vars['edit_collectionId']) {
                return $this->confirmDuplicate($collectionExistId, $title, $titleShort);
            }
        }
        $updateArray['collectionTitle'] = $title;
        if ($titleShort) {
            $updateArray['collectionTitleShort'] = $titleShort;
        } else {
            $this->db->formatConditions(['collectionId' => $this->vars['edit_collectionId']]);
            $this->db->updateNull('collection', 'collectionTitleshort');
        }
        // first, check publishers
        if ($row['collectionType'] == 'book') {
            $miscArray['resourcemiscPublisher'] = $temp['resourcemiscPublisher'] =
                $this->editPublisherDetails('edit_publisherName', 'edit_publisherLocation', 'book', 'edit_resourcemiscPublisher');
            $miscArray['resourcemiscField1'] = $temp['resourcemiscField1'] =
                $this->editPublisherDetails('edit_transPublisherName', 'edit_transPublisherLocation', 'book', 'edit_resourcemiscField1');
        } elseif ($row['collectionType'] == 'proceedings') {
            $miscArray['resourcemiscField1'] = $temp['resourcemiscField1'] =
                $this->editPublisherDetails('edit_conferenceOrganiser', 'edit_conferenceOrganiserLocation', 'conference', 'edit_resourcemiscField1');
        }
        foreach ($this->vars as $key => $value) {
            if (($key == 'edit_collectionId') || ($key == 'edit_collectionTitle') || ($key == 'edit_collectionTitleShort')
                || ($key == 'edit_collectionType') || ($key == 'action') || ($key == 'method') || ($key == 'submit')
                || (mb_strpos($key, 'Creator') === 0)) {
                continue;
            }
            $var = trim($value);
            if ($var) {
                $split = UTF8::mb_explode('_', $key);
                $temp[$split[1]] = $var;
                if (mb_strpos($split[1], 'resourcemisc') === 0) {
                    $miscArray[$split[1]] = $var;
                } elseif (mb_strpos($split[1], 'resourceyear') === 0) {
                    $yearArray[$split[1]] = $var;
                } elseif (mb_strpos($split[1], 'resource') === 0) {
                    $resourceArray[$split[1]] = $var;
                }
            }
        }
        if (!array_key_exists('edit_resourcemiscPeerReviewed', $this->vars)) {
            $miscArray['resourcemiscPeerReviewed'] = $temp['resourcemiscPeerReviewed'] = 'N';
        } else {
            $miscArray['resourcemiscPeerReviewed'] = $temp['resourcemiscPeerReviewed'] = 'Y';
        }
        if (!empty($miscArray)) {
            $this->updateMiscTable($miscArray);
        }
        if (!empty($yearArray)) {
            $this->updateYearTable($yearArray);
        }
        if (!empty($resourceArray)) {
            $this->updateResourceTable($resourceArray);
        }
        // Finally, create default value and write fields in collection table
        $this->db->formatConditions(['collectionId' => $this->vars['edit_collectionId']]);
        $this->db->update('collection', $updateArray);
        // remove cache files for collections
        $this->db->deleteCache('cacheResourceCollections');
        $this->db->deleteCache('cacheMetadataCollections');
        $this->db->deleteCache('cacheResourceCollectionTitles');
        $this->db->deleteCache('cacheResourceCollectionShorts');
        foreach ($temp as $key => $value) {
            if (is_array($value) || $value) {
                $collectionDefaults[$key] = $value;
            }
        }
        // Update collection defaults field
        $this->db->formatConditions(['collectionId' => $this->vars['edit_collectionId']]);
        $this->db->update('collection', ['collectionDefault' => base64_encode(serialize($collectionDefaults))]);
        // send back to editDisplay with success message
        $this->init($this->success->text("collection"));
    }
    /**
     * write to the database
     */
    public function editConfirm()
    {
        $this->gatekeep->init(TRUE); // write access requiring WIKINDX_GLOBAL_EDIT to be TRUE
        if ($this->session->getVar("editLock")) {
            $this->badInput->close($this->errors->text("done", "collection"), $this, 'init');
        }
        if (!array_key_exists('edit_collectionId', $this->vars) || !$this->vars['edit_collectionId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        if (!array_key_exists('edit_collectionExistId', $this->vars) || !$this->vars['edit_collectionExistId']) {
            $this->badInput->close($this->errors->text("inputError", "missing"), $this, 'init');
        }
        $editId = $this->vars['edit_collectionId'];
        $existId = $this->vars['edit_collectionExistId'];
        // Delete old creator
        $this->db->formatConditions(['collectionId' => $editId]);
        $this->db->delete('collection');
        // remove cache files for collections
        $this->db->deleteCache('cacheResourceCollections');
        $this->db->deleteCache('cacheMetadataCollections');
        $this->db->deleteCache('cacheResourceCollectionTitles');
        $this->db->deleteCache('cacheResourceCollectionShorts');
        // Select all resources referencing this old collection and replace reference with existing collection
        $this->db->formatConditions(['resourcemiscCollection' => $editId]);
        $recordset = $this->db->select('resource_misc', ['resourcemiscId', 'resourcemiscCollection']);
        while ($row = $this->db->fetchRow($recordset)) {
            $this->db->formatConditions(['resourcemiscId' => $row['resourcemiscId']]);
            $this->db->update('resource_misc', ['resourcemiscCollection' => $existId]);
        }
        // lock reload
        $this->session->setVar("editLock", TRUE);
        // Clear session
        $this->session->clearArray("edit");
        // send back to editDisplay with success message
        $this->init($this->success->text("collection"));
    }
    /**
     * Setter for loading session variables into $this->sessionVars when editing and using AJAX
     */
    public function setSessionVars()
    {
        $this->sessionVars = $this->session->getArray('edit');
    }
    /**
     * Create form for book collections
     */
    private function bookDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td($this->seriesBook());
        $pString = \HTML\p($tab);
        $tab = \HTML\td($this->pubBook());
        $pString .= \HTML\p($tab);
        $tab = \HTML\td($this->miscBook());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();
        $pString .= \HTML\p($tab);
        $pString .= \HTML\hr();
        if ($creators = $this->creatorFields('book')) {
            $tab = \HTML\tableStart();
            $tab .= $creators;
            $tab .= \HTML\tableEnd();
            $pString .= \HTML\p($tab);
            $pString .= \HTML\hr();
        }
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td($this->transTitle());
        $pString .= \HTML\p($tab);
        $tab = \HTML\td($this->transPub());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();
        $pString .= \HTML\p($tab);

        return $pString;
    }
    /**
     * Create form for journal collections
     */
    private function journalDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab = \HTML\td($this->pubJournal());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();

        return \HTML\p($tab);
    }
    /**
     * Create form for thesis
     */
    private function thesisDefaults()
    {
        return \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->thesis['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        ));
    }
    /**
     * Create form for proceedings
     */
    private function proceedingsDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td($this->conference());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();
        $pString = \HTML\p($tab);
        $pString .= \HTML\hr();
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td($this->seriesProceedings());
        $pString .= \HTML\p($tab);
        $tab = \HTML\td($this->pubProceedings());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();
        $pString .= \HTML\p($tab);
        $pString .= \HTML\hr();
        if ($creators = $this->creatorFields('proceedings')) {
            $tab = \HTML\tableStart();
            $tab .= $creators;
            $tab .= \HTML\tableEnd();
            $pString .= \HTML\p($tab);
            $pString .= \HTML\hr();
        }

        return $pString;
    }
    /**
     * Create form for newspaper collections
     */
    private function newspaperDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td(\HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->newspaper['resource']['Field2']),
            'edit_resourceField2',
            $this->defaultFormElementValue('resourceField2'),
            30
        )));
        $tab .= \HTML\td(\HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->newspaper['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        )));
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();

        return \HTML\p($tab);
    }
    /**
     * Create form for magazine collections
     */
    private function magazineDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td(\HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->magazine['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        )));
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();

        return \HTML\p($tab);
    }
    /**
     * Create form for magazine collections
     */
    private function manuscriptDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td(\HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->manuscript['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        )));
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();

        return \HTML\p($tab);
    }
    /**
     * Create form for web collections
     */
    private function webDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab .= \HTML\td($this->pubWeb());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();
        $pString = \HTML\p($tab);
        $pString .= \HTML\hr();
        if ($creators = $this->creatorFields('web')) {
            $tab = \HTML\tableStart();
            $tab .= $creators;
            $tab .= \HTML\tableEnd();
            $pString .= \HTML\p($tab);
        }

        return $pString;
    }
    /**
     * Create form for music collections
     */
    private function musicDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab = \HTML\td($this->pubMusic());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();

        return \HTML\p($tab);
    }
    /**
     * Create form for miscellaneous collections
     */
    private function miscellaneousDefaults()
    {
        $tab = \HTML\tableStart('generalTable borderStyleSolid');
        $tab .= \HTML\trStart();
        $tab = \HTML\td($this->pubMiscellaneous());
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();

        return \HTML\p($tab);
    }
    /**
     * Creator details
     *
     * @param mixed $type
     * @param mixed $addRemove
     *
     * @return array
     */
    private function creatorFields($type, $addRemove = FALSE)
    {
        if (!$this->getCreators()) {
            return FALSE;
        }
        $label = $this->makeCreatorName();
        foreach ($this->defaultMap->{$type}['resource_creator'] as $creatorRole => $creatorLabel) {
            ${$creatorRole} = $this->getCreatorRoles($creatorRole);
        }
        $creatorFields = '';
        foreach ($this->defaultMap->{$type}['resource_creator'] as $creatorRole => $creatorLabel) {
            if (empty(${$creatorRole})) {
                $creatorFields .= \HTML\tableStart('generalTable borderStyleSolid') .
                    $this->addRemoveCreators('Creator' . $creatorRole, $creatorLabel, FALSE, 0) . \HTML\tableEnd();

                continue;
            }
            $fields = '';
            foreach (${$creatorRole} as $creatorId => $creatorOrder) {
                $fields .= $this->creatorField($creatorOrder, 'Creator' . $creatorRole, $creatorId);
            }
            $fields = \HTML\tableStart() . $label . $fields . \HTML\tableEnd();
            $index = count(${$creatorRole});
            $cell = \HTML\tableStart('generalTable borderStyleSolid')
                . $this->addRemoveCreators('Creator' . $creatorRole, $creatorLabel, $fields, $index)
                . \HTML\tableEnd();
            $creatorFields .= $cell;
        }

        return $creatorFields;
    }
    /**
     * Create HTML for one creator field
     *
     * @param mixed $creatorOrder
     * @param mixed $creatorType
     * @param mixed $creatorId
     *
     * @return array
     */
    private function creatorField($creatorOrder, $creatorType, $creatorId)
    {
        $fields = \HTML\trStart();
        $entry = $creatorType . '_' . $creatorOrder . '_firstname';
        array_key_exists($entry, $this->sessionVars) ? $value = $this->sessionVars[$entry] : $value = FALSE;
        $this->session->setVar("edit_" . $entry, $value);
        $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 30, 255));
        $entry = $creatorType . '_' . $creatorOrder . '_initials';
        array_key_exists($entry, $this->sessionVars) ? $value = $this->sessionVars[$entry] : $value = FALSE;
        $this->session->setVar("edit_" . $entry, $value);
        $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 6, 255));
        $entry = $creatorType . '_' . $creatorOrder . '_prefix';
        array_key_exists($entry, $this->sessionVars) ? $value = $this->sessionVars[$entry] : $value = FALSE;
        $this->session->setVar("edit_" . $entry, $value);
        $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 11, 255));
        $entry = $creatorType . '_' . $creatorOrder . '_surname';
        array_key_exists($entry, $this->sessionVars) ? $value = $this->sessionVars[$entry] : $value = FALSE;
        $this->session->setVar("edit_" . $entry, $value);
        $fields .= \HTML\td(\FORM\textInput(FALSE, $entry, $value, 30, 255));
        $entry = $creatorType . '_' . $creatorOrder . '_select';
        array_key_exists($entry, $this->sessionVars) ? $value = $this->sessionVars[$entry] : $value = $creatorId;
        $this->session->setVar("edit_" . $entry, $value);
        $fields .= \HTML\td(\FORM\selectedBoxValue(FALSE, $entry, $this->creatorsArray, $value, 1));
        $fields .= \HTML\trEnd();

        return $fields;
    }
    /**
     * Get creator roles
     *
     * @param mixed $creatorRole
     *
     * @return array
     */
    private function getCreatorRoles($creatorRole)
    {
        if (!empty($this->sessionVars)) { // back here after a mis-edit so pull any select
            $surnames = [];
            foreach ($this->sessionVars as $key => $value) {
                if (mb_strpos($key, 'Creator') === 0) {
                    $split = UTF8::mb_explode('_', $key);
                    if ($split[2] == 'surname') {
                        trim($value) ? $surnames[$split[0] . '_' . $split[1] . '_select'] = TRUE : $surnames[$split[0] . '_' . $split[1] . '_select'] = FALSE;
                    }
                    if ($split[2] != 'select') {
                        continue;
                    }
                    if ($value) {
                        $this->defaults['creators'][$key] = $value;
                    }
                }
            }
            foreach ($surnames as $key => $value) {
                if (($value !== FALSE) && !array_key_exists($key, $this->defaults['creators'])) {
                    $this->defaults['creators'][$key] = 0;
                }
            }
        }
        if (!array_key_exists('creators', $this->defaults)) {
            return [];
        }
        foreach ($this->defaults['creators'] as $creator => $creatorId) {
            $split = UTF8::mb_explode('_', $creator);
            $role = str_replace('Creator', '', $split[0]);
            if ($role == $creatorRole) {
                ${$creatorRole}[$creatorId] = $split[1];
            }
        }
        if (isset(${$creatorRole})) {
            asort(${$creatorRole});

            return ${$creatorRole};
        }

        return [];
    }
    /**
     * Get creators from database
     *
     * @return bool
     */
    private function getCreators()
    {
        $creatorObj = FACTORY_CREATOR::getInstance();
        $temp = $creatorObj->grabAll();
        $creatorsArray[0] = $this->messages->text("misc", "ignore");
        if (is_array($temp)) {
            $this->creatorsArray = $creatorsArray + $temp;

            return TRUE;
        } else {
            return FALSE;
        }
    }
    /**
     * Add or remove creator cells
     *
     * @param mixed $creatorType
     * @param mixed $creatorMsg
     * @param mixed $fields
     * @param mixed $index
     *
     * @return array
     */
    private function addRemoveCreators($creatorType, $creatorMsg, $fields, $index)
    {
        $jsonArray = [];
        $jScript = "index.php?action=edit_EDITCOLLECTIONAJAX_CORE&method=addCreatorField&creatorType=$creatorType";
        $jsonArray[] = [
            'startFunction' => 'addCreator',
            'script' => "$jScript",
            'creatorType' => "$creatorType",
            'type' => 'edit',
            'index' => "$index",
        ];
        $addImage = \AJAX\jActionIcon('add', 'onclick', $jsonArray);
        $jsonArray = [];
        $jScript = "index.php?action=edit_EDITCOLLECTIONAJAX_CORE&method=removeCreatorField&creatorType=$creatorType";
        $jsonArray[] = [
            'startFunction' => 'removeCreator',
            'script' => "$jScript",
            'creatorType' => "$creatorType",
            'type' => 'edit',
            'index' => "$index",
        ];
        $removeImage = \AJAX\jActionIcon('remove', 'onclick', $jsonArray);
        $images = '&nbsp;&nbsp;' . $addImage . '&nbsp;&nbsp;' . $removeImage;
        $creatorCells = \HTML\trStart();
        $creatorCells .= \HTML\td(\HTML\h($this->messages->text('creators', $creatorMsg) . $images, FALSE, 4), 'width15percent');
        $creatorCells .= \HTML\td(\HTML\div($creatorType . '_Inner', $fields), 'width85percent');
        $creatorCells .= \HTML\trEnd();

        return $creatorCells;
    }
    /**
     * Make the name part labels for the creator fields
     *
     * @return string
     */
    private function makeCreatorName()
    {
        $label = \HTML\trStart();
        $label .= \HTML\td($this->messages->text("resources", "firstname"));
        $label .= \HTML\td($this->messages->text("resources", "initials") .
            BR . \HTML\span($this->messages->text("hint", "initials"), 'hint'));
        $label .= \HTML\td($this->messages->text("resources", "prefix"));
        $label .= \HTML\td($this->messages->text("resources", "surname"));
        $label .= \HTML\td('&nbsp;');
        $label .= \HTML\trEnd();

        return $label;
    }
    /**
     * Get misc. details for books
     *
     * @return string
     */
    private function miscBook()
    {
        $td = \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource_misc']['Field4']),
            'edit_resourcemiscField4',
            $this->defaultFormElementValue('resourcemiscField4'),
            10
        ) . BR .
            \HTML\span($this->messages->text('hint', 'arabicNumeral1'), 'hint'));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['Field4']),
            'edit_resourceField4',
            $this->defaultFormElementValue('resourceField4'),
            10
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'volumeYear'),
            'edit_resourceyearYear3',
            $this->defaultFormElementValue('resourceyearYear3'),
            10
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['Doi']),
            'edit_resourceDoi',
            $this->defaultFormElementValue('resourceDoi'),
            30
        ) .
            BR . \HTML\span($this->messages->text('hint', 'doi'), 'hint'));

        return $td;
    }
    /**
     * Get series title etc. for a book
     *
     * @return string
     */
    private function seriesBook()
    {
        $td = \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['Field1']),
            'edit_resourceField1',
            $this->defaultFormElementValue('resourceField1'),
            60
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['Field3']),
            'edit_resourceField3',
            $this->defaultFormElementValue('resourceField3'),
            10
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['Field2']),
            'edit_resourceField2',
            $this->defaultFormElementValue('resourceField2'),
            10
        ));
        $checked = $this->defaultFormElementValue('resourcemiscPeerReviewed') == 'Y' ? TRUE : FALSE;
        $td .= \HTML\p(\FORM\checkbox(
            $this->messages->text("resources", $this->defaultMap->book['resource_misc']['PeerReviewed']),
            'edit_resourcemiscPeerReviewed',
            $checked
        ));

        return $td;
    }
    /**
     * Get series title etc. for proceedings
     *
     * @return string
     */
    private function seriesProceedings()
    {
        $td = \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->proceedings['resource']['Field1']),
            'edit_resourceField1',
            $this->defaultFormElementValue('resourceField1'),
            60
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->proceedings['resource']['Field3']),
            'edit_resourceField3',
            $this->defaultFormElementValue('resourceField3'),
            10
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->proceedings['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'proceedingsVolumeNumber'),
            'edit_resourceField4',
            $this->defaultFormElementValue('resourceField4'),
            10
        ));
        $checked = $this->defaultFormElementValue('resourcemiscPeerReviewed') == 'Y' ? TRUE : FALSE;
        $td .= \HTML\p(\FORM\checkbox(
            $this->messages->text("resources", $this->defaultMap->proceedings['resource_misc']['PeerReviewed']),
            'edit_resourcemiscPeerReviewed',
            $checked
        ));

        return $td;
    }
    /**
     * Get translated title etc. for this type of resource
     *
     * @return string
     */
    private function transTitle()
    {
        $td = \HTML\em($this->messages->text("resources", "translatedFrom"));
        $title = $this->defaultFormElementValue('resourceTransTitle');
        if ($title && ($noSort = $this->defaultFormElementValue('resourceTransNoSort'))) {
            $select = $noSort . ' ' . $title;
        } elseif ($title) {
            $select = $title;
        } else {
            $select = FALSE;
        }
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['TransTitle']),
            'edit_resourceTransTitle',
            $select,
            80
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['TransSubtitle']),
            'edit_resourceTransSubtitle',
            $this->defaultFormElementValue('resourceTransSubtitle'),
            80
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->book['resource']['TransShortTitle']),
            'edit_resourceTransShortTitle',
            $this->defaultFormElementValue('resourceTransShortTitle'),
            30
        ));

        return $td;
    }
    /**
     * Get conference organizer details for proceedings
     *
     * @return string
     */
    private function conference()
    {
        $publisher = FACTORY_PUBLISHER::getInstance();
        $tab = \HTML\tableStart();
        $tab .= \HTML\trStart();
        $td = \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'conferenceOrganiser'),
            'edit_conferenceOrganiser',
            '',
            40
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'conferenceLocation'),
            'edit_conferenceLocation',
            '',
            40
        ));
        $temp = $publisher->grabAll('conference');
        if (is_array($temp)) {
            $pub = $this->messages->text("resources", "organiser");
            $pubs[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value) {
                $pubs[$key] = preg_replace("/{(.*)}/Uu", "$1", $value);
            }
            $select = $this->defaultFormElementValue('resourcemiscField1');
            if ($select) {
                $td .= \HTML\p(\FORM\selectedBoxValue($pub, 'edit_resourcemiscField1', $pubs, $select, 1));
            } else {
                $td .= \HTML\p(\FORM\selectFBoxValue($pub, 'edit_resourcemiscField1', $pubs, 1));
            }
        }
        $tab .= \HTML\td($td);
        // days
        $days[] = $this->messages->text("misc", "ignore");
        $days = array_merge($days, range(1, 31));
        $select = $this->defaultFormElementValue('resourcemiscField2');
        $td = \HTML\p(\FORM\selectedBoxValue($this->messages->text("resources", 'startDay'), 'edit_resourcemiscField2', $days, $select, 1));
        $select = $this->defaultFormElementValue('resourcemiscField5');
        $td .= \HTML\p(\FORM\selectedBoxValue($this->messages->text("resources", 'endDay'), 'edit_resourcemiscField5', $days, $select, 1));
        $tab .= \HTML\td($td);
        // months
        $constant = FACTORY_CONSTANTS::getInstance();
        $months[] = $this->messages->text("misc", "ignore");
        $months = array_merge($months, $constant->monthToLongName());
        $select = $this->defaultFormElementValue('resourcemiscField3');
        $td = \HTML\p(\FORM\selectedBoxValue($this->messages->text("resources", 'startMonth'), 'edit_resourcemiscField3', $months, $select, 1));
        $select = $this->defaultFormElementValue('resourcemiscField6');
        $td .= \HTML\p(\FORM\selectedBoxValue($this->messages->text("resources", 'endMonth'), 'edit_resourcemiscField6', $months, $select, 1));
        $tab .= \HTML\td($td);
        // years
        $td = \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'startYear'),
            'edit_resourceyearYear2',
            $this->defaultFormElementValue('resourceyearYear2'),
            10
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'endYear'),
            'edit_resourceyearYear3',
            $this->defaultFormElementValue('resourceyearYear3'),
            10
        ));
        $tab .= \HTML\td($td);
        $tab .= \HTML\trEnd();
        $tab .= \HTML\tableEnd();

        return $tab;
    }
    /**
     * General publisher details
     *
     * @param mixed $type
     *
     * @return string
     */
    private function pub($type = FALSE)
    {
        $publisher = FACTORY_PUBLISHER::getInstance();
        $td = \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publisherName'),
            'edit_publisherName',
            '',
            40
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publisherLocation'),
            'edit_publisherLocation',
            '',
            40
        ));
        $temp = $publisher->grabAll($type);
        if (is_array($temp)) {
            $pub = $this->messages->text("resources", "publisher");
            $pubs[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value) {
                $pubs[$key] = preg_replace("/{(.*)}/Uu", "$1", $value);
            }
            $select = $this->defaultFormElementValue('resourcemiscPublisher');
            if ($select) {
                $td .= \HTML\p(\FORM\selectedBoxValue($pub, 'edit_resourcemiscPublisher', $pubs, $select, 1));
            } else {
                $td .= \HTML\p(\FORM\selectFBoxValue($pub, 'edit_resourcemiscPublisher', $pubs, 1));
            }
        }

        return $td;
    }
    /**
     * Get publisher etc. for a book
     *
     * @return string
     */
    private function pubBook()
    {
        $td = $this->pub();
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publicationYear'),
            'edit_resourceyearYear1',
            $this->defaultFormElementValue('resourceyearYear1'),
            10
        ) .
            BR . \HTML\span($this->messages->text('hint', 'publicationYear'), 'hint'));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'reprintYear'),
            'edit_resourceyearYear2',
            $this->defaultFormElementValue('resourceyearYear2'),
            10
        ));

        return $td;
    }
    /**
     * Get publisher etc. for proceedings
     *
     * @return string
     */
    private function pubProceedings()
    {
        $td = $this->pub('conference');
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publicationYear'),
            'edit_resourceyearYear1',
            $this->defaultFormElementValue('resourceyearYear1'),
            10
        ) .
            BR . \HTML\span($this->messages->text('hint', 'publicationYear'), 'hint'));

        return $td;
    }
    /**
     * Get publisher etc. for a journal
     *
     * @return string
     */
    private function pubJournal()
    {
        $td = $this->pub();
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->journal['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        ));
        $checked = $this->defaultFormElementValue('resourcemiscPeerReviewed') == 'Y' ? TRUE : FALSE;
        $td .= \HTML\p(\FORM\checkbox(
            $this->messages->text("resources", $this->defaultMap->journal['resource_misc']['PeerReviewed']),
            'edit_resourcemiscPeerReviewed',
            $checked
        ));

        return $td;
    }
    /**
     * Get publisher etc. for a web site
     *
     * @return string
     */
    private function pubWeb()
    {
        $td = $this->pub();
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->web['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        ));
        $checked = $this->defaultFormElementValue('resourcemiscPeerReviewed') == 'Y' ? TRUE : FALSE;
        $td .= \HTML\p(\FORM\checkbox(
            $this->messages->text("resources", $this->defaultMap->web['resource_misc']['PeerReviewed']),
            'edit_resourcemiscPeerReviewed',
            $checked
        ));

        return $td;
    }
    /**
     * Get publisher etc. for a music collection
     *
     * @return string
     */
    private function pubMusic()
    {
        $td = $this->pub('music');
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->music['resource']['Field2']),
            'edit_resourceField2',
            $this->defaultFormElementValue('resourceField2'),
            30
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->music['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        ));

        return $td;
    }
    /**
     * Get publisher etc. for a music collection
     *
     * @return string
     */
    private function pubMiscellaneous()
    {
        $td = $this->pub();
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publicationYear'),
            'edit_resourceyearYear1',
            $this->defaultFormElementValue('resourceyearYear1'),
            10
        ) .
            BR . \HTML\span($this->messages->text('hint', 'publicationYear'), 'hint'));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->miscellaneous['resource']['Field2']),
            'edit_resourceField2',
            $this->defaultFormElementValue('resourceField2'),
            30
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', $this->defaultMap->miscellaneous['resource']['Isbn']),
            'edit_resourceIsbn',
            $this->defaultFormElementValue('resourceIsbn'),
            30
        ));
        $checked = $this->defaultFormElementValue('resourcemiscPeerReviewed') == 'Y' ? TRUE : FALSE;
        $td .= \HTML\p(\FORM\checkbox(
            $this->messages->text("resources", $this->defaultMap->miscellaneous['resource_misc']['PeerReviewed']),
            'edit_resourcemiscPeerReviewed',
            $checked
        ));

        return $td;
    }
    /**
     * Get translated publisher etc. for this type of resource
     *
     * @return string
     */
    private function transPub()
    {
        $publisher = FACTORY_PUBLISHER::getInstance();
        $td = \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publisherName'),
            'edit_transPublisherName',
            '',
            40
        ));
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publisherLocation'),
            'edit_transPublisherLocation',
            '',
            40
        ));
        $temp = $publisher->grabAll();
        if (!empty($temp)) {
            $pub = $this->messages->text("resources", "publisher");
            $pubs[0] = $this->messages->text("misc", "ignore");
            foreach ($temp as $key => $value) {
                $pubs[$key] = $value;
            }
            $select = $this->defaultFormElementValue('resourcemiscField1');
            if ($select) {
                $td .= \HTML\p(\FORM\selectedBoxValue($pub, 'edit_resourcemiscField1', $pubs, $select, 1));
            } else {
                $td .= \HTML\p(\FORM\selectFBoxValue($pub, 'edit_resourcemiscField1', $pubs, 1));
            }
        }
        $td .= \HTML\p(\FORM\textInput(
            $this->messages->text('resources', 'publicationYear'),
            'edit_resourceyearYear4',
            $this->defaultFormElementValue('resourceyearYear4'),
            10
        ));

        return $td;
    }
    /**
     * Return default value for form element
     *
     * @param array $field
     *
     * @return false|string
     */
    private function defaultFormElementValue($field)
    {
        if (array_key_exists($field, $this->defaults)) {
            return \HTML\dbToFormTidy($this->defaults[$field]);
        } else {
            return FALSE;
        }
    }
    /**
     * Update resource table for all resources in this collection
     *
     * @param array $resourceArray
     */
    private function updateResourceTable($resourceArray)
    {
        $defaults = $nulls = $update = [];
        foreach ($this->defaultMap->{$this->vars['edit_collectionType']}['resource'] as $key => $value) {
            $defaults[] = 'resource' . $key;
        }
        $nulls = array_diff($defaults, array_keys($resourceArray));
        foreach ($resourceArray as $key => $value) {
            if ($value) {
                $update[$key] = $value;
            } elseif (array_search($key, $nulls) === FALSE) {
                $nulls[] = $key;
            }
        }
        if (!empty($update)) {
            $this->db->formatConditions(['resourcemiscCollection' => $this->vars['edit_collectionId']]);
            $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
            $this->db->update('resource', $update);
        }
        if (!empty($nulls)) {
            $this->db->formatConditions(['resourcemiscCollection' => $this->vars['edit_collectionId']]);
            $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceId');
            $this->db->updateNull('resource', $nulls);
        }
    }
    /**
     * Update resource_misc table for all resources in this collection
     *
     * @param array $miscArray
     */
    private function updateMiscTable($miscArray)
    {
        $defaults = $nulls = $update = [];
        foreach ($this->defaultMap->{$this->vars['edit_collectionType']}['resource_misc'] as $key => $value) {
            $defaults[] = 'resourcemisc' . $key;
        }
        $nulls = array_diff($defaults, array_keys($miscArray));
        foreach ($miscArray as $key => $value) {
            if ($value) {
                $update[$key] = $value;
            } elseif (array_search($key, $nulls) === FALSE) {
                $nulls[] = $key;
            }
        }
        if (!empty($update)) {
            $this->db->formatConditions(['resourcemiscCollection' => $this->vars['edit_collectionId']]);
            $this->db->update('resource_misc', $update);
        }
        if (!empty($nulls)) {
            $this->db->formatConditions(['resourcemiscCollection' => $this->vars['edit_collectionId']]);
            $this->db->updateNull('resource_misc', $nulls);
        }
    }
    /**
     * Update resource_year table for all resources in this collection
     *
     * @param array $yearArray
     */
    private function updateYearTable($yearArray)
    {
        $defaults = $nulls = $update = [];
        foreach ($this->defaultMap->{$this->vars['edit_collectionType']}['resource_year'] as $key => $value) {
            $defaults[] = 'resourceyear' . $key;
        }
        $nulls = array_diff($defaults, array_keys($yearArray));
        foreach ($yearArray as $key => $value) {
            if ($value) {
                $update[$key] = $value;
            } elseif (array_search($key, $nulls) === FALSE) {
                $nulls[] = $key;
            }
        }
        if (!empty($update)) {
            $this->db->formatConditions(['resourcemiscCollection' => $this->vars['edit_collectionId']]);
            $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceyearId');
            $this->db->update('resource_year', $update);
        }
        if (!empty($nulls)) {
            $this->db->formatConditions(['resourcemiscCollection' => $this->vars['edit_collectionId']]);
            $this->db->leftJoin('resource_misc', 'resourcemiscId', 'resourceyearId');
            $this->db->updateNull('resource_year', $nulls);
        }
    }
    /**
     * Sort through and delete/add creators.
     * Use values from session as these have been sorted into the correct order.
     * When adding creators, must also add creators to the resources of the collection (possibly re-ordered creators so delete all first).
     * When deleting creators, must also delete from any collection defaults if the deleted creator no longer exists in a resource.
     * $sessionVars contains e.g. Creator2_0_initials and Creator2_0_prefix where '2' refers to creator role and '0' order of the creator (less 1).
     * The collection defaults array has the 'creators' array which is e.g.
     * ( [creators] => Array ( [Creator2_0_select] => 809 [Creator3_0_select] => 812 [Creator4_0_select] => 813 [Creator2_1_select] => 810 ). That is,
     * it only stores creatorIDs. Therefore, collection defaults are written after any creators are added or deleted in the database.
     * Return an array of creators ready to be written to collection defaults.
     *
     * @return array
     */
    private function editCreators()
    {
        $creators = [];
        foreach ($this->sessionVars as $key => $value) {
            if (mb_strpos($key, 'Creator') === 0) {
                $split = UTF8::mb_explode('_', $key);
                $role = str_replace('Creator', '', $split[0]);
                if ($split[2] == 'firstname') {
                    $creators[$role][$split[1]]['firstname'] = trim($value) ? trim($value) : FALSE;
                } elseif ($split[2] == 'initials') {
                    $creators[$role][$split[1]]['initials'] = trim($value) ? trim($value) : FALSE;
                } elseif ($split[2] == 'prefix') {
                    $creators[$role][$split[1]]['prefix'] = trim($value) ? trim($value) : FALSE;
                } elseif ($split[2] == 'surname') {
                    $creators[$role][$split[1]]['surname'] = trim($value) ? trim($value) : FALSE;
                } elseif ($split[2] == 'select') {
                    $creators[$role][$split[1]]['select'] = $value ? $value : FALSE;
                }
            }
        }
        //  Ensure firstname, initials, prefix are FALSE if there is no surname
        foreach ($creators as $role => $creatorArray) {
            foreach ($creatorArray as $order => $creator) {
                if (!$creator['surname']) {
                    $creators[$role][$order]['initials'] = FALSE;
                    $creators[$role][$order]['firstname'] = FALSE;
                    $creators[$role][$order]['prefix'] = FALSE;
                }
            }
        }

        return $this->writeCreators($creators);
    }
    /**
     * Write creators to resources belonging to this collection.
     * First, write details of new creators to the creators table and gather creatorIds
     * Next, gather all resourceIds for this collection.
     * Then, for each creator role that is stored in $creators, delete all creators from the resource_creator table for those resources.
     * Finally, write the new creators and details to the resource_creator table.
     *
     * @param mixed $creators
     *
     * @return array
     */
    private function writeCreators($creators)
    {
        $creatorDefaults = [];
        $creatorObj = FACTORY_CREATOR::getInstance();
        // Write new creators to creators table and store new creatorId
        foreach ($creators as $role => $creatorArray) {
            foreach ($creatorArray as $order => $creator) {
                if ($creator['surname']) { // entry in surname takes precedence
                    $writeArray = [];
                    unset($creator['select']);
                    $initials = $creatorObj->formatInitials($creator['initials']);
                    $creatorId = $creatorObj->checkExists(
                        $creator['surname'],
                        $creator['firstname'],
                        $initials,
                        $creator['prefix']
                    );
                    if (!$creatorId) { // new creator
                        $writeArray['creatorSurname'] = $creator['surname'];
                        if ($creator['firstname']) {
                            $writeArray['creatorFirstname'] = $creator['firstname'];
                        }
                        if ($creator['prefix']) {
                            $writeArray['creatorPrefix'] = $creator['prefix'];
                        }
                        if ($initials) {
                            $writeArray['creatorInitials'] = $initials;
                        }
                        $this->db->insert('creator', array_keys($writeArray), array_values($writeArray));
                        $creatorId = $this->db->lastAutoID();
                    }
                    $creators[$role][$order]['select'] = $creatorId;
                }
            }
        }
        $writeArray = [];
        $this->db->formatConditions(['resourcemiscCollection' => $this->vars['edit_collectionId']]);
        $this->db->leftJoin('resource_creator', 'resourcecreatorResourceId', 'resourcemiscId');
        $resultSet = $this->db->select('resource_misc', ['resourcemiscId', 'resourcecreatorCreatorMain', 'resourcecreatorCreatorSurname'], TRUE);
        while ($row = $this->db->fetchRow($resultSet)) {
            // Delete all entries in resource_creator for this resourceId where resourcecreatorRole != 1
            $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourcemiscId']]);
            $this->db->formatConditions(['resourcecreatorRole' => '1'], TRUE);
            $this->db->delete('resource_creator');
            $writeArray['resourcecreatorResourceId'] = $row['resourcemiscId'];
            $writeArray['resourcecreatorCreatorMain'] = $row['resourcecreatorCreatorMain'];
            // remove all punctuation (keep apostrophe and dash for names such as Grimshaw-Aagaard and D'Eath)
            $writeArray['resourcecreatorCreatorSurname'] =
                mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $row['resourcecreatorCreatorSurname']));
            foreach ($creators as $role => $creatorArray) {
                foreach ($creatorArray as $order => $creator) {
                    $writeArray['resourcecreatorCreatorId'] = $creator['select'];
                    $writeArray['resourcecreatorOrder'] = $order + 1;
                    $writeArray['resourcecreatorRole'] = $role;
                    $this->db->insert('resource_creator', array_keys($writeArray), array_values($writeArray));
                    $defaultKey = 'Creator' . $role . '_' . $order . '_select';
                    $creatorDefaults[$defaultKey] = $creator['select'];
                }
            }
            // Check if there are creators listed in resource_creator for these resources
            $this->db->formatConditions(['resourcecreatorResourceId' => $row['resourcemiscId']]);
            $resultSet2 = $this->db->select('resource_creator', 'resourcecreatorId');
            if (!$this->db->numRows($resultSet2)) { // need blank row for list operations
                $this->db->insert('resource_creator', ['resourcecreatorResourceId'], [$row['resourcemiscId']]);
            }
        }
        // remove cache files for creators
        $this->db->deleteCache('cacheResourceCreators');
        $this->db->deleteCache('cacheMetadataCreators');
        $creatorObj->removeHanging(FALSE);

        return $creatorDefaults;
    }
    /**
     * Edit publisher and translated publisher details
     *
     * @param mixed $name
     * @param mixed $location
     * @param mixed $type
     * @param mixed $id
     *
     * @return mixed
     */
    private function editPublisherDetails($name, $location, $type, $id)
    {
        $pubObject = FACTORY_PUBLISHER::getInstance();
        $returnValue = FALSE;
        if (array_key_exists($name, $this->vars) && UTF8::mb_trim($this->vars[$name])) {
            $name = UTF8::mb_trim($this->vars[$name]);
            if (array_key_exists($location, $this->vars)) {
                $location = UTF8::mb_trim($this->vars[$location]);
            } else {
                $location = '';
            }
            if ($publisherExistsId = $pubObject->checkExists($name, $location)) {
                $this->vars[$id] = $returnValue = $publisherExistsId;
            } else { // need to write new publisher to publisher table
                $fields[] = 'publisherName';
                $values[] = $name;
                if ($location) {
                    $fields[] = 'publisherLocation';
                    $values[] = $location;
                }
                $fields[] = 'publisherType';
                $values[] = $type;
                $this->db->insert('publisher', $fields, $values);
                $this->vars[$id] = $returnValue = $this->db->lastAutoId();
            }
        } elseif (array_key_exists($id, $this->vars) && ($this->vars[$id] == 0)) { // i.e. 'IGNORE'
            unset($this->vars[$id]);
            $returnValue = FALSE;
        }
        unset($this->vars[$name]);
        unset($this->vars[$location]);
        $pubObject->removeHanging();
        $this->db->deleteCache('cacheResourcePublishers');
        $this->db->deleteCache('cacheMetadataPublishers');

        return $returnValue;
    }
    /**
     * The new collection equals one already in the database. Confirm that this edited one is to be removed and
     * all references to it replaced by the existing one.
     *
     * @param mixed $collectionExistId
     * @param mixed $title
     * @param mixed $titleShort
     */
    private function confirmDuplicate($collectionExistId, $title, $titleShort)
    {
        GLOBALS::setTplVar('heading', $this->messages->text("heading", "edit", " (" .
            $this->messages->text("resources", "collection") . ")"));
        $pString = $this->errors->text("warning", "collectionExists");
        $pString .= \HTML\p($this->messages->text("misc", "collectionExists"));
        $pString .= \FORM\formHeader("edit_EDITCOLLECTION_CORE");
        $pString .= \FORM\hidden("edit_collectionId", $this->vars['edit_collectionId']);
        $pString .= \FORM\hidden("edit_collectionExistId", $collectionExistId);
        $pString .= \FORM\hidden("method", 'editConfirm');
        $pString .= \HTML\p(\FORM\formSubmit($this->messages->text("submit", "Proceed")), FALSE, "right");
        $pString .= \FORM\formEnd();
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Write session variables for creators to session array 'edit' when using AJAX
     *
     * @param mixed $array array of session names and values collected via AJAX
     *
     * @return array array of session names and values
     */
    private function writeSessionCreators($array)
    {
        $temp = $this->session->getArray('edit');
        if (!empty($array['creatorFields'])) {
            // $array['creatorFields'] does not store fields set to IGNORE or blank so, if creator fields don't exist, remove them from the session.
            foreach ($temp as $key => $value) {
                if (!array_key_exists($key, $array['creatorFields']) && (mb_strpos($key, 'Creator') === 0)) {
                    $this->session->delVar("edit_$key");
                }
            }
            $this->session->writeArray($array['creatorFields'], 'edit');
        }
        // else remove all creator fields
        else {
            foreach ($temp as $key => $value) {
                if (mb_strpos($key, 'Creator') === 0) {
                    $this->session->delVar("edit_$key");
                }
            }
        }
        $this->sessionVars = $this->session->getArray('edit');
    }
    /**
     * Write edit_ vars to session
     */
    private function writeVarsToSession()
    {
        $this->session->clearArray('edit');
        $allCreators = [];
        foreach ($this->vars as $key => $value) {
            if (mb_strpos($key, 'edit_') === 0) {
                if ($key == 'edit_resourcemiscPeerReviewed') {
                    $this->sessionVars[str_replace('edit_', '', $key)] = 'Y';

                    continue;
                }
                $this->sessionVars[str_replace('edit_', '', $key)] = $value;
            }
            // Write creator session data and ensure each creator in each role is correctly ordered
            elseif (mb_strpos($key, 'Creator') === 0) {
                $split = UTF8::mb_explode('_', $key);
                $newKey = $split[0] . '_' . $split[1];
                $allCreators[$split[0]]['order'][$newKey] = $split[1];
                if ($split[2] == 'initials') {
                    $allCreators[$split[0]]['creators'][$newKey]['initials'] = $value;
                } elseif ($split[2] == 'prefix') {
                    $allCreators[$split[0]]['creators'][$newKey]['prefix'] = $value;
                } elseif ($split[2] == 'firstname') {
                    $allCreators[$split[0]]['creators'][$newKey]['firstname'] = $value;
                } elseif ($split[2] == 'surname') {
                    $allCreators[$split[0]]['creators'][$newKey]['surname'] = $value;
                } elseif ($split[2] == 'select') {
                    $allCreators[$split[0]]['creators'][$newKey]['select'] = $value;
                }
            }
        }
        if (!empty($allCreators)) {
            $temp = $allCreators;
            foreach ($temp as $role => $array) { // remove empty creators lacking _select or _surname
                foreach ($array['creators'] as $key => $array2) {
                    if (!trim($array2['surname']) && !$array2['select']) {
                        unset($allCreators[$role]['creators'][$key]);
                        unset($allCreators[$role]['order'][$key]);
                    }
                }
            }
            foreach ($allCreators as $role => $array) {
                $index = 0;
                asort($array['order']);
                foreach ($array['order'] as $key => $value) {
                    $newKey = $role . '_' . $index;
                    $sessionKey = $newKey . '_initials';
                    $this->sessionVars[$sessionKey] = $allCreators[$role]['creators'][$key]['initials'];
                    $sessionKey = $newKey . '_prefix';
                    $this->sessionVars[$sessionKey] = $allCreators[$role]['creators'][$key]['prefix'];
                    $sessionKey = $newKey . '_firstname';
                    $this->sessionVars[$sessionKey] = $allCreators[$role]['creators'][$key]['firstname'];
                    $sessionKey = $newKey . '_surname';
                    $this->sessionVars[$sessionKey] = $allCreators[$role]['creators'][$key]['surname'];
                    $sessionKey = $newKey . '_select';
                    $this->sessionVars[$sessionKey] = $allCreators[$role]['creators'][$key]['select'];
                    ++$index;
                }
            }
        }
        $this->session->writeArray($this->sessionVars, 'edit');
        $this->sessionVars = $this->session->getArray('edit'); // also picks up creator fields
    }
}
