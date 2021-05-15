<?php

/** Disable version checker
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, https://www.vrana.cz/
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/

if (!defined('WIKINDX_ADMINER_CALLER')) {
    header("HTTP/1.1 403 Forbidden");
    die("Access forbidden.");
}

class AdminerVersionNoverify {
	
	function head() {
		echo script("verifyVersion = function () {};");
		return true;
	}
	
}
