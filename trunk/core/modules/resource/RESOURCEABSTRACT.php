<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * RESOURCEABSTRACT class
 *
 * View resource's abstract
 */
class RESOURCEABSTRACT
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
        $this->userId = $this->session->getVar('setup_UserId');
    }
    /**
     * Display resource's abstract
     *
     * @param array $row
     *
     * @return array
     */
    public function view($row)
    {
        $abstract = [];
        $write = $this->session->getVar('setup_Write') ? TRUE : FALSE;
        if (!$row['resourcetextAbstract'] && !$write)
        {
            return $abstract;
        }
        if ($this->session->getVar("setup_Superadmin") ||
            ($write && (!WIKINDX_ORIGINATOR_EDIT_ONLY || ($row['resourcemiscAddUserIdResource'] == $this->userId))))
        {
            if (!$row['resourcetextAbstract'])
            {
                $abstract['title'] = $this->messages->text("resources", "abstract");
                $abstract['editLink'] = \HTML\a(
                    $this->icons->getClass("add"),
                    $this->icons->getHTML("add"),
                    "index.php?action=metadata_EDITMETADATA_CORE" .
                    htmlentities("&type=abstractInit&id=" . $row['resourceId'])
                );

                return $abstract;
            }
            elseif ($row['resourcetextAbstract'])
            {
                $abstract['editLink'] = \HTML\a(
                    $this->icons->getClass("edit"),
                    $this->icons->getHTML("edit"),
                    "index.php?action=metadata_EDITMETADATA_CORE" .
                    htmlentities("&type=abstractInit&id=" . $row['resourceId'])
                );
                $abstract['deleteLink'] = \HTML\a(
                    $this->icons->getClass("delete"),
                    $this->icons->getHTML("delete"),
                    "index.php?action=metadata_EDITMETADATA_CORE" .
                    htmlentities("&type=abstractDeleteInit&id=" . $row['resourceId'])
                );
            }
        }
        if ($row['resourcetextAbstract'])
        {
            $abstract['title'] = $this->messages->text("resources", "abstract");
            list($abstract['userAdd'], $abstract['userEdit']) = $this->user->displayUserAddEdit($row, TRUE, 'abstract');
            $abstract['abstract'] =
                $this->cite->parseCitations($this->common->doHighlight(\HTML\dbToHtmlTidy($row['resourcetextAbstract'])), 'html');
        }

        return $abstract;
    }
}
