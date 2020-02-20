<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * BADINPUT
 *
 * Display various error messages when input has gone wrong and, if necessary, redirect and close the script
 *
 * If an $object and $method are provided, it is expected that that method will exit the script tidily itself and will
 * provide a means to print $error.
 * Otherwise, $closeType calls the CLOSExxx object to exit (this is the default).
 *
 * @package wikindx\core\navigation
 */
class BADINPUT
{
    /** string */
    public $closeType = FALSE;

    /**
     * BADINPUT
     */
    public function __construct()
    {
        $this->closeType = 'close'; // default
    }
    /**
     * Close and exit the current script operation.
     *
     * @param string $error Error message
     * @param object $object Reference to object. Default is FALSE
     * @param mixed $method If string, method in $object. If array, 1st element is method in $object followed by other messages to pass to the method. Default is FALSE
     */
    public function close($error, &$object = FALSE, $method = FALSE)
    {
        if (is_array($error))
        {
            $errors = $error;
        }
        else
        {
            $errors = [$error];
        }
        foreach ($errors as $error)
        {
            if ($object)
            {
                if (is_array($method))
                {
                    $methodName = array_shift($method);
                    $error = [$error];
                    while ($method)
                    {
                        $error[] = array_shift($method);
                    }
                }
                else
                {
                    $methodName = $method;
                }
                if (!$methodName)
                {
                    echo "Object defined but no method defined for object in BADINPUT";
                    die;
                }
                else
                {
                    $object->{$methodName}($error);
                }
            }
            else
            {
                GLOBALS::addTplVar('content', $error);
            }
        }
        $this->loadClose();
    }
    /**
     * Load the CLOSExxx object
     */
    private function loadClose()
    {
        switch (mb_strtolower($this->closeType))
        {
            case 'close':
                FACTORY_CLOSE::getInstance();

            break;
            case 'closenomenu':
                FACTORY_CLOSENOMENU::getInstance();

            break;
            case 'closepopup':
                FACTORY_CLOSEPOPUP::getInstance();

            break;
            default:
                print "Incorrect CLOSE object defined in BADINPUT";
                die;

            break;
        }
    }
}
