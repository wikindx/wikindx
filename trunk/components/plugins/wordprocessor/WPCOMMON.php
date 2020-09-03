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
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));

echo '<script src="' . WIKINDX_BASE_URL . '/core/tiny_mce/tiny_mce_popup.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
echo '<script src="' . WIKINDX_BASE_URL . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/wordprocessor/wikindxWPcommon.js?ver=' . WIKINDX_PUBLIC_VERSION . '"></script>';
$class = new WPCommon();

include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));

class WPCommon
{
    private $pluginmessages;
    private $session;
    private $vars;
    private $db;
    private $papersDir = WIKINDX_DIR_DATA_PLUGINS . DIRECTORY_SEPARATOR . "wordprocessor";

    public function __construct()
    {
        $this->pluginmessages = new PLUGINMESSAGES('wordprocessor', 'wordprocessorMessages');
        $this->session = FACTORY_SESSION::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->db = FACTORY_DB::getInstance();
    }
    /**
     * Save the file
     */
    public function save()
    {
        $saveAsNewVersion = FALSE;
        $text = $this->vars['hdnpaperText'];
        $title = trim($this->vars['title']);
        if (!$title || !preg_match("/^[A-Za-z0-9_ ]+$/u", $title)) {
            $this->failure("<span class=\\'error\\'>" . $this->pluginmessages->text("invalidTitle") . "</span>", base64_decode($this->session->getVar("wp_Title")));
        }
        $userId = $this->session->getVar("setup_UserId");
        $hashFileName = sha1($userId . $title . $text);
        if (array_key_exists('saveAsNewVersion', $this->vars) && ($title != base64_decode($this->session->getVar("wp_Title")))) {
            $saveAsNewVersion = TRUE;
        }
        // inserting
        if (!array_key_exists('id', $this->vars) || $saveAsNewVersion) {
            $fields[] = 'pluginwordprocessorHashFilename';
            $values[] = $hashFileName;
            $fields[] = 'pluginwordprocessorUserId';
            $values[] = $userId;
            $fields[] = 'pluginwordprocessorFilename';
            $values[] = $title;
            $fields[] = 'pluginwordprocessorTimestamp';
            $values[] = $this->db->formatTimestamp();
            $this->db->insert('plugin_wordprocessor', $fields, $values);
            $databaseId = $this->db->lastAutoId();
        }
        // updating
        else {
            $updateArray['pluginwordprocessorHashFilename'] = $hashFileName;
            $updateArray['pluginwordprocessorFilename'] = $title;
            $updateArray['pluginwordprocessorTimestamp'] = $this->db->formatTimestamp();
            $databaseId = $this->vars['id'];
            $this->db->formatConditions(['pluginwordprocessorId' => $this->vars['id']]);
            $this->db->update('plugin_wordprocessor', $updateArray);
        }
        $fullFileName = $this->papersDir . DIRECTORY_SEPARATOR . $hashFileName;
        if ($fp = fopen("$fullFileName", "w")) {
            if (!$text) {
                $text = ' '; // fputs won't write empty string.
            }
            if (!fwrite($fp, $text)) {
                $this->failure("<span class=\\'error\\'>" . $this->pluginmessages->text("saveFailure") . "</span>", base64_decode($this->session->getVar("wp_Title")));
            }

            fclose($fp);
        } else {
            $this->failure("<span class=\\'error\\'>" . $this->pluginmessages->text("saveFailure") . "</span>", base64_decode($this->session->getVar("wp_Title")));
        }

        // if this is a re-save, remove old hashed file from folder if it's not the same and we're not saving a new version
        if (array_key_exists('hashFilename', $this->vars) &&
            ($this->vars['hashFilename'] != $hashFileName) &&
            file_exists($this->papersDir . DIRECTORY_SEPARATOR . $this->vars['hashFilename']) &&
            !$saveAsNewVersion) {
            unlink($this->papersDir . DIRECTORY_SEPARATOR . $this->vars['hashFilename']);
        }
        $this->session->setVar("wp_HashFilename", $hashFileName);
        $this->session->setVar("wp_Id", $databaseId);
        $this->session->setVar("wp_Title", base64_encode($title));
        $pString = "<script type=\"text/javascript\">tinyMCEPopup.close();</script>";
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Bomb out
     *
     * @param mixed $message
     * @param mixed $title
     */
    public function failure($message, $title)
    {
        $pString .= "<script type=\"text/javascript\">var wpStatus=parent.opener.document.getElementById('wpStatus');wpStatus.innerHTML=\"$message\";var wpTitle=parent.opener.document.getElementById('wpTitle');wpTitle.innerHTML=\"$title\";tinyMCEPopup.close();</script>";
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
}
