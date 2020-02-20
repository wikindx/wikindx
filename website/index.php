<?php

/*
Copyright Stéphane Aulery, 2017

<lkppo@users.sourceforge.net>

This software is a computer program whose purpose is to power the wikindx's
official website.

This software is governed by the CeCILL-C license under French law and
abiding by the rules of distribution of free software.  You can  use, 
modify and/ or redistribute the software under the terms of the CeCILL-C
license as circulated by CEA, CNRS and INRIA at the following URL
"http://www.cecill.info". 

As a counterpart to the access to the source code and  rights to copy,
modify and redistribute granted by the license, users are provided only
with a limited warranty  and the software's author,  the holder of the
economic rights,  and the successive licensors  have only  limited
liability. 

In this respect, the user's attention is drawn to the risks associated
with loading,  using,  modifying and/or developing or reproducing the
software by the user in light of its specific status of free software,
that may mean  that it is complicated to manipulate,  and  that  also
therefore means  that it is reserved for developers  and  experienced
professionals having in-depth computer knowledge. Users are therefore
encouraged to load and test the software's suitability as regards their
requirements in conditions enabling the security of their systems and/or 
data to be ensured and,  more generally, to use and operate it in the 
same conditions as regards security. 

The fact that you are presently reading this means that you have had
knowledge of the CeCILL-C license and that you accept its terms.
*/

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'lib.php');

// If no page name is provided, the default page displayed is 'about'
// otherwise if the directory is missing or empty, display a 404 error page

define('PAGE_DEFAULT', 'about');
define('PAGE_HTTP_ERROR_404', '404');

if (!isset($_GET['page'])) $_GET['page'] = PAGE_DEFAULT;

if (!in_array($_GET['page'], dirInDirToArray(__DIR__))) $_GET['page'] = PAGE_HTTP_ERROR_404;

$BodyPartArray = fileInDirToArray(__DIR__ . DIRECTORY_SEPARATOR . $_GET['page']);

$NbPart = 0;
foreach ($BodyPartArray as $file)
{
    if (mb_strtolower(mb_substr($file, -4)) == '.php')
    {
        $NbPart++;
        break;
    }
}

if ($NbPart == 0)
{
    $_GET['page'] = PAGE_HTTP_ERROR_404;
    $BodyPartArray = fileInDirToArray(__DIR__ . DIRECTORY_SEPARATOR . $_GET['page']);
}

if ($_GET['page'] == PAGE_HTTP_ERROR_404)
{
    header('HTTP/1.0 404 Not Found');
}

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'header.php');

// Build the page body with the content of each file
// contained in the directory of the same name
foreach ($BodyPartArray as $file)
{
    if (mb_strtolower(mb_substr($file, -4)) == '.php')
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . $_GET['page'] . DIRECTORY_SEPARATOR . $file);
    }
}

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'footer.php');

?>