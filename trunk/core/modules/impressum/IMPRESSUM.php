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
 * IMPRESSUM
 *
 * @package wikindx\core\modules\impressum
 */
class IMPRESSUM
{
    // Constructor
    public function init()
    {

        GLOBALS::addTplVar('content', WIKINDX_IMPRESSUM);

        FACTORY_CLOSE::getInstance();
    }
}
