<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 *	HELPMESSAGES class
 *
 *	Context help
 */
class HELPMESSAGES
{
    private $vars;
    private $help;
    private $messages;
    private $errors;
    private $badInput;
    private $icons;
    private $session;

    public function __construct()
    {
        $this->vars = GLOBALS::getVars();
        $this->help = FACTORY_HELP::getInstance();
        $this->errors = FACTORY_ERRORS::getInstance();
        $this->badInput = FACTORY_BADINPUT::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();

        $this->icons = FACTORY_LOADICONS::getInstance('help');
        $this->session = FACTORY_SESSION::getInstance();
        GLOBALS::setTplVar('heading', '');
    }
    /**
     * init
     */
    public function init()
    {
        if (array_key_exists('message', $this->vars) && $this->vars['message']) {
            GLOBALS::addTplVar('content', $this->help->text($this->vars['message']) . \HTML\p(\FORM\closePopup($this->messages->text("misc", "closePopup"))));
        } else {
            $this->badInput->closeType = 'closePopup';
            $this->badInput->close($this->errors->text('inputError', 'missing'));
        }
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * Create a popup link for a Htlp message
     *
     * @param mixed $message
     *
     * @return string HTML link tag
     */
    public function createLink($message)
    {
        $jScript = "javascript:coreOpenPopup('index.php?action=help_HELPMESSAGES_CORE&amp;message=" . $message . "', 80)";

        return \HTML\a($this->icons->getClass("help"), $this->icons->getHTML("help"), $jScript);
    }
}
