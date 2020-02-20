<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * EDITCOLLECTIONAJAX -- AJAX for editing collections
 */
class EDITCOLLECTIONAJAX
{
    private $vars;
    private $collectionForm;

    public function __construct()
    {
        $this->vars = GLOBALS::getVars();


        include('core/modules/edit/EDITCOLLECTION.php');
        $this->collectionForm = new EDITCOLLECTION();
    }
    /**
     * Add a creator input field (AJAX)
     */
    public function addCreatorField()
    {
        if ($fields = $this->creatorFields('add'))
        {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', \HTML\tableStart('borderStyleSolid') . $fields . \HTML\tableEnd());
        }
        else
        {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', '&nbsp;');
        }
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * remove a creator input field (AJAX)
     */
    public function removeCreatorField()
    {
        if ($fields = $this->creatorFields('remove'))
        {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', \HTML\tableStart('borderStyleSolid') .
                $fields . \HTML\tableEnd());
        }
        else
        {
            $div = \HTML\div($this->vars['creatorType'] . '_Inner', '&nbsp;');
        }
        $jsonResponseArray = [
            'innerHTML' => "$div",
        ];
        if (is_array(error_get_last()))
        {
            // NB E_STRICT in PHP5 gives warning about use of GLOBALS below.  E_STRICT cannot be controlled through WIKINDX
            $error = error_get_last();
            $error = $error['message'];
            GLOBALS::addTplVar('content', \AJAX\encode_jArray(['ERROR' => $error]));
        }
        else
        {
            GLOBALS::addTplVar('content', \AJAX\encode_jArray($jsonResponseArray));
        }
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Cycle creator fields and make label row (AJAX)
     *
     * @param mixed $addRemove
     *
     * @return string|FALSE
     */
    private function creatorFields($addRemove)
    {
        $jArray = \AJAX\decode_jString($this->vars['ajaxReturn']);

        return $this->collectionForm->doAddRemoveCreator($this->vars['creatorType'], $addRemove, $jArray);
    }
}
