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
 * Generalized CURL function.
 *
 * @package wikindx\core\modules\curl
 */
class CURL
{
    /**
     * mass cache of attachments
     */
    public function attachmentCache()
    {
        // HTTP charset (HTTP specification doesn't permit to declare Content-type separately)
        header('Content-type: ' . WIKINDX_MIMETYPE_TXT . '; charset=' . WIKINDX_CHARSET);
        // Define a custom header that point to the hash of the parsed file
        // This is mandatory because the output of PdfToText could be altered at byte level
        header('resourceattachmentsHashFilename: ' . $_GET['id']);
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "list", "FILETOTEXT.php"]));
        $ftt = new FILETOTEXT();
        echo $ftt->convertToText($_GET['file'], $_GET['fileType']);
        FACTORY_CLOSERAW::getInstance(); // die
    }
}
