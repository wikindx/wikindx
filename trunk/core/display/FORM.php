<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */


/**
 * Miscellaneous FORM elements
 *
 * @package wikindx\core\display
 */
namespace FORM
{
    /**
     * FORM widgets class
     */
    const FORM_CLASS = 'formElements'; // see .css file in template
    /**
     * Build a string for insertion of an HTML tag attribute.
     *
     * Ensures that the attribute value is never empty (incorrect syntax)
     *
     * @param string $name
     * @param string $value Default is ''
     *
     * @return string
     */
    function _inlineHtmlAttribute($name = '', $value = '')
    {
        return ' ' . rtrim($name) . '="' . $value . '"';
    }
    /**
     * print form header with hidden action field
     *
     * $js is for javascript functions
     *
     * @param string $action
     * @param string $js Default is ''
     *
     * @return string
     */
    function formHeader($action, $js = '')
    {
        $pString = '<form method="post" ' . $js . '>';

        $pString .= $action ? \FORM\hiddenNoJS('action', $action) : '';

        return $pString;
    }
    /**
     * print form header with visible action field -- typically used for tinyMCE popups
     *
     * $js is for javascript functions
     *
     * @param string $action
     * @param string $name
     * @param string $js Default is ''
     *
     * @return string
     */
    function formHeaderVisibleAction($action, $name, $js = '')
    {
        $pString = '<form '
            . \FORM\_inlineHtmlAttribute('action', $action)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('method', 'post')
            . ' ' . $js . '>';

        return $pString;
    }
    /**
     * print form header with hidden action field and name and id fields
     *
     * js is for javascript functions
     *
     * @param string $action
     * @param string $name
     * @param string $js Default is ''
     *
     * @return string
     */
    function formHeaderName($action, $name, $js = '')
    {
        $pString = '<form '
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('method', 'post')
            . ' ' . $js . '>';

        $pString .= $action ? \FORM\hiddenNoJS('action', $action) : '';

        return $pString;
    }
    /**
     * end a form
     *
     * @return string
     */
    function formEnd()
    {
        return '</form>';
    }
    /**
     * print form header with hidden action field for multi-part upload forms
     *
     * @param string $action
     * @param string $js Default is ''
     *
     * @return string
     */
    function formMultiHeader($action, $js = '')
    {
        $pString = "<form enctype=\"multipart/form-data\" method=\"post\" $js>";

        $pString .= \FORM\hiddenNoJS('action', $action);

        return $pString;
    }
    /**
     * print form footer with submit field
     *
     * @param string $value Default is FALSE
     * @param string $name Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function formSubmit($value = FALSE, $name = FALSE, $js = '')
    {
        if (!$name)
        {
            $name = 'submit';
        }

        $pString = '<input'
            . \FORM\_inlineHtmlAttribute('type', 'submit')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * print form footer with close popup button
     *
     * @param mixed $value
     *
     * @return string
     */
    function closePopup($value)
    {
        $name = 'cancel';

        $pString = '<input'
            . \FORM\_inlineHtmlAttribute('type', 'button')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('onclick', 'coreClosePopup()')
            . ' value="' . $value . '" >';

        return $pString;
    }
    /**
     * print form footer with submit button field
     *
     * @param string $value Default is FALSE
     * @param string $name Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function formSubmitButton($value, $name = FALSE, $js = '')
    {
        if (!$name)
        {
            $name = 'submit';
        }

        $pString = '<input'
            . \FORM\_inlineHtmlAttribute('type', 'button')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * print form reset button
     *
     * @param string $js Default is ''
     * @param mixed $value
     *
     * @return string
     */
    function formReset($value, $js = '')
    {
        $pString = '<input'
            . \FORM\_inlineHtmlAttribute('type', 'reset')
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * print hidden form input
     *
     * @param string $name
     * @param string $value
     * @param string $js Default is ''
     *
     * @return string
     */
    function hidden($name, $value, $js = '')
    {
        $pString = '<input'
            . \FORM\_inlineHtmlAttribute('type', 'hidden')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * print hidden form input without JavaScript action
     *
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    function hiddenNoJS($name, $value)
    {
        return '<input'
            . \FORM\_inlineHtmlAttribute('type', 'hidden')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . ' value="' . $value . '">';
    }
    /**
     * print radio button
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param string $checked Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function radioButton($label, $name, $value = FALSE, $checked = FALSE, $js = '')
    {
        $checked ? $checked = ' checked' : '';

        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'radio')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '"'
            . ' ' . $checked . ' ' . $js . '>';

        return $pString;
    }
    /**
     * print checkbox
     *
     * @param string $label
     * @param string $name
     * @param string $checked Default is FALSE
     * @param string $title Default is ''
     * @param string $js Default is ''
     *
     * @return string
     */
    function checkbox($label, $name, $checked = FALSE, $title = '', $js = '')
    {
        $checked ? $checked = ' checked' : '';

        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'checkbox')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('title', $title)
            . ' ' . $checked . ' ' . $js . '>';

        return $pString;
    }
    /**
     * create select boxes for HTML forms
     *
     * First OPTION is always SELECTED
     * optional $override allows the programmer to override the user set preferences for character limiting in select boxes
     *
     * @param string $label
     * @param string $name
     * @param array $array
     * @param int $size Default  is 3
     * @param int $override Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function selectFBox($label, $name, $array, $size = 3, $override = FALSE, $js = '')
    {
        if ($label)
        {
            $pString = $label . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<select'
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . ' ' . $js . '>' . LF;

        $value = array_shift($array);
        $string = \FORM\reduceLongText($value, $override);
        if (!empty($array))
        {
            $pString .= "<option value=\"$value\" selected>" . $string . '</option>' . LF;
        }
        if (is_array($array))
        {
            foreach ($array as $value)
            {
                $string = \FORM\reduceLongText($value, $override);
                $pString .= "<option value=\"$value\">$string</option>" . LF;
            }
        }

        $pString .= '</select>';

        return $pString;
    }
    /**
     * create select boxes for HTML forms
     *
     * 'selected value' is set SELECTED
     * optional $override allows the programmer to override the user set preferences for character limiting in select boxes
     *
     * @param string $label
     * @param string $name
     * @param array $array
     * @param string $select
     * @param int $size Default  is 3
     * @param int $override Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function selectedBox($label, $name, $array, $select, $size = 3, $override = FALSE, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<select'
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . ' ' . $js . '>' . LF;

        if (is_array($array))
        {
            foreach ($array as $value)
            {
                $string = \FORM\reduceLongText($value, $override);
    
                if ($value == $select)
                {
                    $pString .= "<option value=\"$value\" selected>$string</option>" . LF;
                }
                else
                {
                    $pString .= "<option>$string</option>" . LF;
                }
            }
        }

        $pString .= '</select>';

        return $pString;
    }
    /**
     * create select boxes for HTML forms
     *
     * First entry is default selection.
     * OPTION VALUE is set so expects assoc. array where key holds this value
     * optional $override allows the programmer to override the user set preferences for character limiting in select boxes
     *
     * @param string $label
     * @param string $name
     * @param array $array
     * @param int $size Default  is 3
     * @param int $override Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function selectFBoxValue($label, $name, $array, $size = 3, $override = FALSE, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<select'
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . ' ' . $js . '>' . LF;

        if (!empty($array))
        {
            $pString .= "<option value=\"" . key($array) . "\" selected>" .
                \FORM\reduceLongText(current($array), $override) . '</option>' . LF;
            $doneFirst = FALSE;
        }
        if (is_array($array))
        {
            foreach ($array as $key => $value)
            {
                $value = \FORM\reduceLongText($value, $override);
                if (!$doneFirst)
                {
                    $doneFirst = TRUE;

                    continue;
                }
                $pString .= "<option value=\"$key\">$value</option>" . LF;
            }
        }

        $pString .= '</select>';

        return $pString;
    }
    /**
     * create select boxes for HTML forms
     *
     * $select is default selection.
     * OPTION VALUE is set so expects assoc. array where key holds this value
     * optional $override allows the programmer to override the user set preferences for character limiting in select boxes
     *
     * @param string $label
     * @param string $name
     * @param array $array
     * @param string $select
     * @param int $size Default  is 3
     * @param int $override Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function selectedBoxValue($label, $name, $array, $select, $size = 3, $override = FALSE, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<select'
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . ' ' . $js . '>' . LF;

        if (is_array($array))
        {
            foreach ($array as $key => $value)
            {
                $value = \FORM\reduceLongText($value, $override);
                ($key == $select) ?
                    $pString .= "<option value=\"$key\" selected>$value</option>" . LF :
                    $pString .= "<option value=\"$key\">$value</option>" . LF;
            }
        }

        $pString .= '</select>';

        return $pString;
    }
    /**
     * create select boxes for HTML forms
     *
     * First entry is default selection.
     * OPTION VALUE is set so expects assoc. array where key holds this value.
     * MULTIPLE values may be selected
     * optional $override allows the programmer to override the user set preferences for character limiting in select boxes
     *
     * @param string $label
     * @param string $name
     * @param array $array
     * @param int $size Default  is 3
     * @param int $override Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function selectFBoxValueMultiple($label, $name, $array, $size = 3, $override = FALSE, $js = '')
    {
        $id = $name;
        $name .= '[]';

        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<select'
            . \FORM\_inlineHtmlAttribute('id', $id)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . ' multiple ' . $js . '>' . LF;

        if (!empty($array))
        {
            $pString .= "<option value=\"" . key($array) . "\" selected>" .
                \FORM\reduceLongText(current($array), $override) . "</option>" . LF;
            $doneFirst = FALSE;
        }
        if (is_array($array))
        {
            foreach ($array as $key => $value)
            {
                $value = \FORM\reduceLongText($value, $override);
                if (!$doneFirst)
                {
                    $doneFirst = TRUE;

                    continue;
                }
                $pString .= "<option value=\"$key\">$value</option>" . LF;
            }
        }
        $pString .= '</select>';

        return $pString;
    }
    /**
     * create select boxes for HTML forms
     *
     * OPTION VALUE is set so expects assoc. array where key holds this value.
     * MULTIPLE values may be selected
     * optional $override allows the programmer to override the user set preferences for character limiting in select boxes
     *
     * @param string $label
     * @param string $name
     * @param array $array
     * @param array $values
     * @param int $size Default  is 3
     * @param int $override Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function selectedBoxValueMultiple($label, $name, $array, $values, $size = 3, $override = FALSE, $js = '')
    {
        $id = $name;
        $name .= '[]';

        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<select'
            . \FORM\_inlineHtmlAttribute('id', $id)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . ' multiple ' . $js . '>' . LF;

        if (is_array($array))
        {
            foreach ($array as $key => $value)
            {
                $value = \FORM\reduceLongText($value, $override);
                if ((array_search($key, $values) !== FALSE) && $key)
                {
                    $pString .= "<option value=\"$key\" selected>" . $value . "</option>" . LF;
                }
                else
                {
                    $pString .= "<option value=\"$key\">$value</option>" . LF;
                }
            }
        }
        //		$pString .= implode('', array_values($array));

        $pString .= '</select>';

        return $pString;
    }
    /**
     * password input type
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param int $size Default is 20
     * @param int $maxLength Default is 255
     * @param string $js Default is ''
     *
     * @return string
     */
    function passwordInput($label, $name, $value = FALSE, $size = 20, $maxLength = 255, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'password')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . \FORM\_inlineHtmlAttribute('maxLength', $maxLength)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * text input type
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param int $size Default is 20
     * @param int $maxLength Default is 255
     * @param string $js Default is ''
     *
     * @return string
     */
    function textInput($label, $name, $value = FALSE, $size = 20, $maxLength = 255, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'text')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . \FORM\_inlineHtmlAttribute('maxLength', $maxLength)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * color input type
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function colorInput($label, $name, $value = FALSE, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'color')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * textarea input type
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param int $cols Default is 30
     * @param int $rows Default is 5
     * @param string $js Default is ''
     *
     * @return string
     */
    function textareaInput($label, $name, $value = FALSE, $cols = 30, $rows = 5, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<textarea'
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('cols', $cols)
            . \FORM\_inlineHtmlAttribute('rows', $rows)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . " $js>$value</textarea>";

        return $pString;
    }
    /**
     * textarea input type without MCE editor
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param int $cols Default is 30
     * @param int $rows Default is 5
     * @param string $js Default is ''
     *
     * @return string
     */
    function textareaInputmceNoEditor($label, $name, $value = FALSE, $cols = 30, $rows = 5, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<textarea'
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('cols', $cols)
            . \FORM\_inlineHtmlAttribute('rows', $rows)
            . \FORM\_inlineHtmlAttribute('class', 'mceNoEditor')
            . " $js>$value</textarea>";

        return $pString;
    }
    /**
     * textarea readonly
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param int $cols Default is 30
     * @param int $rows Default is 5
     * @param string $js Default is ''
     *
     * @return string
     */
    function textareaReadonly($label, $name, $value = FALSE, $cols = 30, $rows = 5, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<textarea'
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('cols', $cols)
            . \FORM\_inlineHtmlAttribute('rows', $rows)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . " readonly $js>$value</textarea>";

        return $pString;
    }
    /**
     * upload box
     *
     * @param string $label
     * @param string $name
     * @param int $size Default is 30
     * @param string $js Default is ''
     *
     * @return string
     */
    function fileUpload($label, $name, $size = 20, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'file')
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . " $js>";

        return $pString;
    }
    /**
     * upload box for multiple files
     *
     * @param string $label
     * @param array $name
     * @param int $size Default is 30
     * @param string $js Default is ''
     *
     * @return string
     */
    function fileUploadMultiple($label, $name, $size = 20, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }
        
        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'file')
            . \FORM\_inlineHtmlAttribute('multiple', 'multiple')
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('size', $size)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . " $js>";
        
        return $pString;
    }
    /**
     * date input type
     *
     * @param string $label
     * @param string $name
     * @param string $value Default is FALSE
     * @param string $js Default is ''
     *
     * @return string
     */
    function dateInput($label, $name, $value = FALSE, $js = '')
    {
        if ($label)
        {
            $pString = $label . ':' . BR;
        }
        else
        {
            $pString = '';
        }

        $pString .= '<input'
            . \FORM\_inlineHtmlAttribute('type', 'date')
            . \FORM\_inlineHtmlAttribute('id', $name)
            . \FORM\_inlineHtmlAttribute('name', $name)
            . \FORM\_inlineHtmlAttribute('class', \FORM\FORM_CLASS)
            . ' value="' . $value . '" ' . $js . '>';

        return $pString;
    }
    /**
     * reduce the size of long text (in select boxes usually) to keep web browser display tidy
     *
     * optional $override allows the programmer to override the user set preferences
     *
     * @param string $text
     * @param string $override Default is FALSE
     *
     * @return string
     */
    function reduceLongText($text, $override = FALSE)
    {
        $config = \FACTORY_CONFIG::getInstance();
        // On setup, WIKINDX_STRINGLIMIT is not yet defined
        $userStringLimit = \GLOBALS::getUserVar('StringLimit', WIKINDX_STRINGLIMIT_DEFAULT);
        $limit = $override ? $override : $userStringLimit;
        $text = str_replace("&nbsp;", " ", $text);
        $count = mb_strlen($text);
        if (($limit != -1) && ($count > $limit))
        {
            $start = 0;
            $length = floor(($limit / 2) - 2);
            $substr1 = mb_substr($text, $start, $length);
            $start = $count - $length;
            $substr2 = mb_substr($text, $start, $length);
            $text = $substr1 . ' ... ' . $substr2;
        }
        $text = str_replace(" ", "&nbsp;", $text);

        return $text;
    }
}
