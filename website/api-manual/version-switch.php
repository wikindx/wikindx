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
 * This script return a list of API Manual versions in json format
 *
 * Example: {"trunk":"trunk","6.4.1":"6.4.1"}
 *
 * Called from the API Manual menu to build the version dropdown list.
 *
 * This script must be installed at https://wikindx.sourceforge.io/api-manual/version-switch.php
*/

header("Content-type: application/json; charset=UTF-8");
header('Access-Control-Allow-Origin: *');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

$WebsiteVersionList = [];

foreach(dirInDirToArray(__DIR__) as $v)
{
    $WebsiteVersionList[$v] = $v;
}

krsort($WebsiteVersionList, SORT_NATURAL);

echo json_encode($WebsiteVersionList);
die();


///////////////////////////////////////////////////////////////////////
/// LIB
///////////////////////////////////////////////////////////////////////

function dirToArray($dir)
{
    $result = array();
    
    if (file_exists($dir))
    {
	    $cdir = scandir($dir);
	    
	    if ($cdir !== FALSE)
	    {
	        foreach ($cdir as $k => $v)
	        {
	            if (!in_array($v, array('.', '..')))
	                $result[] = $v;
	        }
	    }
	    
	    unset($cdir);
    }
    
    return $result;
}

function dirInDirToArray($dir)
{
    $result = array();
    
    $cdir = dirToArray($dir);
    
    if (count($cdir) > 0)
    {
        foreach ($cdir as $k => $v)
        {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $v)) 
                $result[] = $v;
        }
    }
    
    unset($cdir);
    
    return $result;
}
