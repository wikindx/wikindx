<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */
class backupmysqlMessages
{
    /** array */
    public $text = [];

    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "Backup Database"),
            "heading" => dgettext($domain, "Backup Database"),
            "backup" => dgettext($domain, "Backup"),
            "noWrite" => dgettext($domain, "cache/plugins/backupmysql/ is not writeable by the web server user.  It currently has the permissions: ###"),
            "deleted" => dgettext($domain, "Files deleted"),
            "newFileName" => dgettext($domain, "New file name"),
            "rename" => dgettext($domain, "Rename file"),
            "renamed" => dgettext($domain, "File successfully renamed"),
        ];
    }
}
