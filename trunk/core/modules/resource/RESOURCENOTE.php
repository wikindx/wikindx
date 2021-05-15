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
 * RESOURCENOTE class
 *
 * Deal with resource's notes
 */
class RESOURCENOTE
{
    private $db;
    private $vars;
    private $session;
    private $messages;
    private $user;
    private $icons;
    private $common;
    private $cite;
    private $userId;
    private $browserTabID = FALSE;

    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->session = FACTORY_SESSION::getInstance();
        $this->messages = FACTORY_MESSAGES::getInstance();
        $this->user = FACTORY_USER::getInstance();
        $this->icons = FACTORY_LOADICONS::getInstance();
        $this->common = FACTORY_RESOURCECOMMON::getInstance();
        $this->cite = FACTORY_CITE::getInstance();
        $this->userId = $this->session->getVar("setup_UserId");
        $this->browserTabID = GLOBALS::getBrowserTabID();
    }
    /**
     * Display resource's note
     *
     * @param array $row
     *
     * @return array
     */
    public function view($row)
    {
        $note = [];
        $write = $this->session->getVar("setup_Write") ? TRUE : FALSE;
        if (!$row['resourcetextNote'] && !$write)
        {
            return $note;
        }
        if ($this->session->getVar("setup_Superadmin") ||
            ($write && (!WIKINDX_ORIGINATOR_EDIT_ONLY || ($row['resourcemiscAddUserIdResource'] == $this->userId))))
        {
            if (!$row['resourcetextNote'])
            {
                $note['title'] = $this->messages->text("viewResource", "notes");
                $note['editLink'] = \HTML\a(
                    $this->icons->getClass("add"),
                    $this->icons->getHTML("add"),
                    "index.php?action=metadata_EDITMETADATA_CORE" .
                    htmlentities("&type=noteInit&id=" . $row['resourceId'] . '&browserTabID=' . $this->browserTabID)
                );

                return $note;
            }
            elseif ($row['resourcetextNote'])
            {
                $note['editLink'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=metadata_EDITMETADATA_CORE" .
                    htmlentities("&type=noteInit&id=" . $row['resourceId'] . '&browserTabID=' . $this->browserTabID)
                );
                $note['deleteLink'] = \HTML\a(
                    $this->icons->getClass("delete"),
                    $this->icons->getHTML("delete"),
                    "index.php?action=metadata_EDITMETADATA_CORE" .
                    htmlentities("&type=noteDeleteInit&id=" . $row['resourceId'] . '&browserTabID=' . $this->browserTabID)
                );
            }
        }
        if ($row['resourcetextNote'])
        {
            $note['title'] = $this->messages->text("viewResource", "notes");
            list($note['userAdd'], $note['userEdit']) = $this->user->displayUserAddEdit($row, TRUE, 'note');
            $note['note'] =
                $this->cite->parseCitations(
                    $this->common->doHighlight(\HTML\nlToHtml($row['resourcetextNote'])),
                    'html'
                );
        }

        return $note;
    }
}
