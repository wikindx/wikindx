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
 * RESOURCEFORMAJAX -- AJAX for resource input form
 */
class RESOURCEFORMAJAX
{
    private $messages;
    private $errors;
    private $resourceMap;
    private $typeMaps;
    private $vars;
    private $db;
    private $resourceForm;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->resourceMap = FACTORY_RESOURCEMAP::getInstance();
        include('core/modules/resource/RESOURCEFORM.php');
        $this->resourceForm = new RESOURCEFORM();
        $this->resourceForm->setFormData();
        $this->typeMaps = $this->resourceMap->getTypeMap();
    }
    /**
     * initCreators
     */
    public function initCreators()
    {
        if (!array_key_exists('resourcecreator', $this->typeMaps[$this->vars['ajaxReturn']]) ||
            !$this->typeMaps[$this->vars['ajaxReturn']]['resourcecreator'] ||
            !array_key_exists('ajaxReturn', $this->vars) || !$this->vars['ajaxReturn']) {
            $div = \HTML\div('creatorsOuter', '&nbsp;');
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => "$div"]));
            FACTORY_CLOSERAW::getInstance(); // die;
        }
        $creatorCells = FALSE;
        foreach ($this->typeMaps[$this->vars['ajaxReturn']]['resourcecreator'] as $key => $creatorMsg) {
            $creatorCells .= $this->resourceForm->blankCreatorCell($key, $creatorMsg);
        }
        if ($creatorCells) {
            $div = \HTML\div('creatorsOuter', \HTML\tableStart('borderStyleSolid') . $creatorCells . \HTML\tableEnd());
        }
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'creatorsOuter',
            'targetState' => 'visible',
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Add a creator input field
     */
    public function addCreatorField()
    {
        if ($fields = $this->creatorFields()) {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', \HTML\tableStart('borderStyleSolid') .
                $fields . \HTML\tableEnd());
        } else {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', '&nbsp;');
        }
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * remove a creator input field
     */
    public function removeCreatorField()
    {
        if ($fields = $this->creatorFields()) {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', \HTML\tableStart('borderStyleSolid') .
                $fields . \HTML\tableEnd());
        } else {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', '&nbsp;');
        }
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in creator details if a conference is selected in the conference select box or a collection is selected
     */
    public function fillCreators()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
            $this->vars['ajaxReturn'] = $this->vars['resourceType'];
        }
        $this->initCreators();
    }
    /**
     * Fill in publisher detail if a conference is selected in the conference select box or a collection is selected
     */
    public function fillPublisher()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        }
        $div = $this->resourceForm->optionalCells('publisher');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'publisherOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in organizer detail if a proceedings collection is selected
     */
    public function fillOrganizer()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        }
        $div = $this->resourceForm->optionalCells('collection'); //collection is organizer
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'organizerOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Publisher
     */
    public function initPublisher()
    {
        $this->resourceForm->setResourceType($this->vars['ajaxReturn']);
        if (array_key_exists('publisher', $this->typeMaps[$this->vars['ajaxReturn']]['optional'])) {
            $visibility = 'visible';
        } else {
            $visibility = 'hidden';
        }
        $div = $this->resourceForm->optionalCells('publisher');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'publisherOuter',
            'targetState' => "$visibility",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in series detail if a series is selected in the series select box or a collection is selected
     */
    public function fillSeries()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        } else {
            $this->resourceForm->seriesFill = $this->vars['ajaxReturn'];
        }
        $div = $this->resourceForm->optionalCells('series');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'seriesOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Series DIV
     */
    public function initSeries()
    {
        $this->resourceForm->setResourceType($this->vars['ajaxReturn']);
        if (array_key_exists('series', $this->typeMaps[$this->vars['ajaxReturn']]['optional'])) {
            $visibility = 'visible';
        } else {
            $visibility = 'hidden';
        }
        $div = $this->resourceForm->optionalCells('series');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'seriesOuter',
            'targetState' => "$visibility",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in organizer detail if a conference is selected in the conference select box
     */
    public function fillCollection()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
        $div = $this->resourceForm->optionalCells('collection');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'organizerOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Conference organizer
     */
    public function initCollection()
    {
        $this->resourceForm->setResourceType($this->vars['ajaxReturn']);
        if (array_key_exists('collection', $this->typeMaps[$this->vars['ajaxReturn']]['optional'])) {
            $visibility = 'visible';
        } else {
            $visibility = 'hidden';
        }
        $div = $this->resourceForm->optionalCells('collection');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'organizerOuter',
            'targetState' => "$visibility",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in conference detail if a conference is selected in the conference select box
     */
    public function fillConference()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        }
        $div = $this->resourceForm->optionalCells('conference');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'conferenceOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Conferences
     */
    public function initConference()
    {
        $this->resourceForm->setResourceType($this->vars['ajaxReturn']);
        if (array_key_exists('conference', $this->typeMaps[$this->vars['ajaxReturn']]['optional'])) {
            $visibility = 'visible';
        } else {
            $visibility = 'hidden';
        }
        $div = $this->resourceForm->optionalCells('conference');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'conferenceOuter',
            'targetState' => "$visibility",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in isbn detail if a collection is selected
     */
    public function fillIsbn()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        }
        $div = $this->resourceForm->divIsbn();
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'isbnOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in doi detail if a collection is selected
     */
    public function fillDoi()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        }
        $div = $this->resourceForm->divDoi();
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'doiOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in translation detail if a translated book is selected in the translated book select box or a collection is selected
     */
    public function fillTrans()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        } else {
            $this->resourceForm->translationFill = $this->vars['ajaxReturn'];
        }
        $div = $this->resourceForm->optionalCells('translation');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'translationOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * For book, book_article, book_chapter:  translated work details
     */
    public function initTranslation()
    {
        $this->resourceForm->setResourceType($this->vars['ajaxReturn']);
        if (array_key_exists('translation', $this->typeMaps[$this->vars['ajaxReturn']]['optional'])) {
            $visibility = 'visible';
        } else {
            $visibility = 'hidden';
        }
        $div = $this->resourceForm->optionalCells('translation');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'translationOuter',
            'targetState' => "$visibility",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Fill in miscellaneous detail if a collection is selected
     */
    public function fillMiscellaneous()
    {
        $this->resourceForm->setResourceType($this->vars['resourceType']);
        if (array_key_exists('fromCollection', $this->vars)) {
            $this->resourceForm->collectionFill = $this->vars['ajaxReturn'];
            $this->resourceForm->getCollectionDefaults();
        }
        $div = $this->resourceForm->optionalCells('miscellaneous');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'miscellaneousOuter',
            'targetState' => "visible",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Miscellaneous details
     */
    public function initMiscellaneous()
    {
        $this->resourceForm->setResourceType($this->vars['ajaxReturn']);
        if (array_key_exists('miscellaneous', $this->typeMaps[$this->vars['ajaxReturn']]['optional'])) {
            $visibility = 'visible';
        } else {
            $visibility = 'hidden';
        }
        $div = $this->resourceForm->optionalCells('miscellaneous');
        $jsonResponseArray = [
            'innerHTML' => "$div",
            'next' => 'TRUE',
            'startFunction' => 'divVisibility',
            'targetDiv' => 'miscellaneousOuter',
            'targetState' => "$visibility",
        ];
        if (is_array(error_get_last())) {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        } else {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * AJAX-based DIV content creator for subcategories
     */
    public function initSubcategories()
    {
        $this->category = FACTORY_CATEGORY::getInstance();
        // if no ajaxReturn, quietly exit
        $div = \HTML\td(\HTML\div('subcategory', "&nbsp;")); // default
        if (array_key_exists('ajaxReturn', $this->vars)) {
            $this->subcategories = $this->category->grabSubAll(TRUE, FALSE, \UTF8\mb_explode(',', $this->vars['ajaxReturn']));
            $this->grabPreviouslySelected('ajaxReturn2');
            if (is_array($this->subcategories)) {
                $div = $this->resourceForm->subcategoryBox($this->subcategories);
            }
        }
        GLOBALS::addTplVar('content', \AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Return an error code to sendback to AJAX when RESOURCEFORM fails to validate
     */
    public function validate()
    {
        $field = $this->vars['field'];
        $error = $this->vars['error'];
        $div = $this->errors->text('inputError', $error, '&nbsp;&nbsp;' . $field);
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Cycle creator fields and make label row
     *
     * @return array|false
     */
    private function creatorFields()
    {
        $jArray = \AJAX\decode_jString($this->vars['ajaxReturn']);

        return $this->resourceForm->creatorFields($this->vars['creatorType'], $jArray);
    }
    /**
     * Store previously selected options
     *
     * @param mixed $qsElement
     */
    private function grabPreviouslySelected($qsElement)
    {
        if (array_key_exists($qsElement, $this->vars)) {
            $this->previousSelect = \UTF8\mb_explode(',', $this->vars[$qsElement]);
            if (($index = array_search(0, $this->previousSelect)) !== FALSE) {
                unset($this->previousSelect[$index]); // remove 'IGNORE' selected
            }
        }
    }
}
