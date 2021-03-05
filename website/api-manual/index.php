<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */

/*
 * This script redirect to the trunk API manual if someone access the api-manual folder.
 *
 * This script must be installed at https://wikindx.sourceforge.io/api-manual/index.php
 */

header("Location: https://wikindx.sourceforge.io/api-manual/trunk/", TRUE, 301);
