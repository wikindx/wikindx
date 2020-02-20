<?php

/*
Copyright Stéphane Aulery, 2019

<lkppo@users.sourceforge.net>

This software is a computer program used to detect and build locales
of the current system.

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

// Extract the name localized of all locales from
// 
// - https://github.com/umpirsky/locale-list
// - https://github.com/umpirsky/country-list
//
// Before you run this script, you must unarchive the contents
// of the previous repositories in the same directory as this script

// base.json was composed by hand from the result of the command "locale -a"
// on Windows, MAC and Openbsd, and
// https://www.gnu.org/software/gettext/manual/html_node/Usual-Language-Codes.html#Usual-Language-Codes.
// Only UTF-8 variants were selected.

// The missing country names are taken from
// 
// https://www.gnu.org/software/gettext/manual/html_node/Country-Codes.html#Country-Codes

$dirpath_language_list = implode(DIRECTORY_SEPARATOR, [__DIR__, "locale-list-master", "data"]);
$dirpath_country_list = implode(DIRECTORY_SEPARATOR, [__DIR__, "country-list-master", "data"]);
$dirpath_base_list = implode(DIRECTORY_SEPARATOR, [__DIR__, "base.json"]);

$baselist = json_decode(file_get_contents($dirpath_base_list), TRUE);
$availablelocales = dirToArray($dirpath_language_list);
$availablecountry = dirToArray($dirpath_country_list);

$aloclist = [];

$i = 0;
foreach($baselist as $loc => $locNameDefault)
{
    $locName = "";
    $countryName = "";
    
    if (stripos($loc, "_"))
        $loccode = substr($loc, 0, stripos($loc, "_"));
    else
        $loccode = $loc;
    
    if (stripos($loc, "_"))
        $countrycode = substr($loc, stripos($loc, "_") + 1);
    else
        $countrycode = "";
    
    // LANGUAGE SEARCH
    
    // Search if we can find the name of the language for the language and country defined
    if (in_array($loc, $availablelocales))
    {
        $locNameLocalized = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, [$dirpath_language_list, $loc, "locales.json"])), TRUE);
        
        if (array_key_exists($loc, $locNameLocalized))
        {
            $locName = $locNameLocalized[$loc];
        }
    }
    
    // Search if we can find the name of the language for the language defined without country
    if ($locName == "" && $loccode != $loc)
    {
        if (in_array("en", $locNameLocalized))
        {
            $locNameLocalized = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, [$dirpath_language_list, "en", "locales.json"])), TRUE);
            
            if (array_key_exists($loccode, $locNameLocalized))
            {
                $locName = $locNameLocalized[$loccode];
            }
        }
    }
    
    // Search if we can find the name of the language for the language defined in a previous match
    // If faut que la liste soit triée par code pour que cela fonctionne.
    // ll_CC will take the name of an ll entry
    if ($locName == "" && array_key_exists($loccode, $aloclist))
    {
        $locName = $aloclist[$loccode];
    }
    
    // Search if we can find the name of the language for the language defined in the english (en) catalog
    if ($locName == "")
    {
        if (in_array("en", $availablecountry))
        {
            $countryNameLocalized = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, [$dirpath_country_list, "en", "country.json"])), TRUE);
            if (array_key_exists($countrycode, $countryNameLocalized))
            {
                $countryName = $countryNameLocalized[$countrycode];
            }
        }
    }
    
    // Search if we can find the name of the language for the language defined in base.json
    // This is done to be able to force a value manually if it is not otherwise identifiable.
    if ($locName == "")
    {
        $locName = $locNameDefault;
    }
    
    
    // COUNTRY SEARCH
    if ($countrycode != "")
    {
        // Search if we can find the name of the country for the language and country defined
        if (in_array($loc, $availablecountry))
        {
            $countryNameLocalized = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, [$dirpath_country_list, $loc, "country.json"])), TRUE);
            if (array_key_exists($countrycode, $countryNameLocalized))
            {
                $countryName = $countryNameLocalized[$countrycode];
            }
        }
        
        // Search if we can find the name of the country for the language defined without country
        if ($countryName == "")
        {
            if (in_array($loccode, $availablecountry))
            {
                $countryNameLocalized = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, [$dirpath_country_list, $loccode, "country.json"])), TRUE);
                if (array_key_exists($countrycode, $countryNameLocalized))
                {
                    $countryName = $countryNameLocalized[$countrycode];
                }
            }
        }
        
    // Search if we can find the name of the country for the country defined in the english (en) catalog
        if ($countryName == "")
        {
            if (in_array("en", $availablecountry))
            {
                $countryNameLocalized = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, [$dirpath_country_list, "en", "country.json"])), TRUE);
                if (array_key_exists($countrycode, $countryNameLocalized))
                {
                    $countryName = $countryNameLocalized[$countrycode];
                }
            }
        }
    }
    
    // Capilalize and add the country name if the locale is national
    if ($countryName != "" && !stripos($locName, "("))
    {
        $countryName = utf8_ucfirst($countryName);
        $locName .= " (" . $countryName . ")";
    }
    
    $locName = utf8_ucfirst($locName);
    $aloclist[$loc] = $locName;
    
    $i++;
    echo $loc . ":" . $locName . ":" . $i . "\n";
}


// Delete a national variant if it is the only one of a language.
$aTmpLoc = [];

foreach($aloclist as $loc => $locNameDefault)
{
    if (stripos($loc, "_"))
        $loccode = substr($loc, 0, stripos($loc, "_"));
    else
        $loccode = $loc;
        
    if (!array_key_exists($loccode, $aTmpLoc))
    {
        $aTmpLoc[$loccode] = 0;
    }
    
    $aTmpLoc[$loccode] = $aTmpLoc[$loccode] + 1;
}

foreach($aTmpLoc as $loc => $count)
{
    if ($count <= 2)
    {
        foreach($aloclist as $locCountry => $locNameDefault)
        {
            if (stripos($locCountry, "_") && substr($locCountry, 0, strlen($loc)) == $loc)
            {
                unset($aloclist[$locCountry]);
            }
        }
    }
}

echo "Write languages_all.json\n";
file_put_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, "languages_all.json"]), json_encode($aloclist, JSON_PRETTY_PRINT));




// Lib
////////////////////////////////////////////////////////////////////////////////

function dirToArray($dir)
{
    $result = array();

    if (file_exists($dir))
    {
	    $cdir = scandir($dir);

	    if ($cdir !== FALSE)
	    {
	        foreach ($cdir as $v)
	        {
	            if (!in_array($v, array('.', '..')))
	                $result[] = $v;
	        }
	    }

	    unset($cdir);
    }

    return $result;
}

function utf8_ucfirst($str)
{
    $fc = mb_substr($str, 0, 1);
    return mb_strtoupper($fc) . mb_substr($str, 1, mb_strlen($str));
}