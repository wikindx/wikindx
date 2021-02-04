<?php

/*
ISC License

Copyright (c) 2017, Stéphane Aulery, <lkppo@users.sourceforge.net>

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
*/

/*
 * his script return a list of version in json format
 *
 * Example: {"trunk":"trunk","6.4.1":"6.4.1"}
 *
 * Called from the website menu to build the version dropdown list. 
*/

header("Content-type: application/json; charset=UTF-8");
header('Access-Control-Allow-Origin: *');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

$WebsiteVersionList = [];

foreach(dirInDirToArray(__DIR__ . DIRECTORY_SEPARATOR . "..") as $v)
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
