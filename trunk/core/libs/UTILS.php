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
 * Miscellaneous UTILS functions
 *
 * @package wikindx\core\libs\UTILS
 */
namespace UTILS
{
    include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "bibcitation", "LOADSTYLE.php"]));

    /**
     * Return an array of mandatory PHP extensions used by the Wikindx core
     *
     * The name of each extension is the value of an array entry. An extension name
     * use the same casing as get_loaded_extensions().
     *
     * @return string[]
     */
    function listCoreMandatoryPHPExtensions()
    {
        return ['Core', 'date', 'fileinfo', 'filter', 'gd', 'gettext', 'hash', 'iconv', 'intl', 'json', 'mbstring', 'libxml','mysqli', 'pcre', 'session', 'SimpleXML', 'xmlreader'];
    }
    /**
     * Return an array of optional PHP extensions used by the Wikindx core
     *
     * The name of each extension is the value of an array entry. An extension name
     * use the same casing as get_loaded_extensions().
     *
     * @return string[]
     */
    function listCoreOptionalPHPExtensions()
    {
        return ['bzip2', 'curl', 'enchant', 'openssl', 'Phar', 'pspell', 'sockets', 'zlib', 'zip'];
    }
    /**
     * Return the normalized name of the current os
     *
     * Values can be:
     * - "windows" for Cygwin, mingw, msys, or Windows
     * - "mac" forall version of MAC OSX
     * - The raw name in lowercase (a-z chars only) for others
     *
     * @return string
     */
    function OSName()
    {
        // Normalize the name of the current system
        // See https://en.wikipedia.org/wiki/Uname
        $system = php_uname("s");
        $system = strtolower($system);
        $system = preg_replace("/[^a-zA-Z]/u", "-", $system);
        $system = explode("-", $system);
        
        // Compute the locale for an environment variable
        // Warning: this value is not necessarily the same as the one recognized by setlocale()
        if (array_intersect($system, ["cygwin", "mingw", "msys", "windows"])) { // Windows
            return "windows";
        } elseif (array_intersect($system, ["darwin"])) { // OSX
            return "mac";
        } else { // Others OS
            return $system;
        }
    }
    
    /**
     * Check if a prefix match against a string
     *
     * @param $string A string
     * @param $prefix A prefix
     *
     * @return bool
     */
    function matchPrefix($string, $prefix)
    {
        return (mb_strtolower(mb_substr($string, 0, mb_strlen($prefix))) == $prefix);
    }
    
    /**
     * Check if a suffix match against a string
     *
     * @param $string A string
     * @param $suffix A suffix
     *
     * @return bool
     */
    function matchSuffix($string, $suffix)
    {
        return (mb_strtolower(mb_substr($string, -mb_strlen($suffix))) == $suffix);
    }
    
    /**
     * Read and return the list of all components installed
     *
     * Status of activation are kept in /data/components.json file, other data are in /cache/components.json file
     * because the status must be be persistent between two upgrades, or a component is uninstalled.
     * This function merges status after reading the two files.
     *
     * Use only this function to read the components list.
     *
     * @param bool $force Forces the cache overwriting if it already exists (optional, FALSE by default)
     *
     * @return array
     */
    function readComponentsList($force = FALSE)
    {
        refreshComponentsListCache($force);
        $path_components_list = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "components.json"]);
        $path_components_list_status = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA, "components.json"]);
        
        $ComponentsList = \FILE\read_json_file($path_components_list);
        $ComponentsListStatus = \FILE\read_json_file($path_components_list_status);
            
        foreach ($ComponentsList as $ki => $ci) {
            // Search installed components with a cached status
            $isStatusDefined = FALSE;
            foreach ($ComponentsListStatus as $kr => $cr) {
                if ($ci["component_type"] == $cr["component_type"] && $ci["component_id"] == $cr["component_id"]) {
                    $ComponentsList[$ki]["component_status"] = $cr["component_status"];
                    $isStatusDefined = TRUE;

                    break;
                }
            }
            // Search installed components without a cached status
            if (!$isStatusDefined) {
                $ComponentsList[$ki]["component_status"] = "disabled";
            }
        }

        return $ComponentsList;
    }
    
    /**
     * Write a list of components from an array
     *
     * Status of activation are kept in /data/components.json file, other data are in /cache/components.json file
     * because the status must be be persistent between two upgrades, or a component is uninstalled.
     * This function separates the status from the other fields and saves them in separate files.
     *
     * Use only this function to save the components list.
     *
     * @param array $ComponentsList Components list (with activation status)
     */
    function writeComponentsList($ComponentsList)
    {
        $path_components_list = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "components.json"]);
        $path_components_list_status = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA, "components.json"]);
        
        if (file_exists($path_components_list_status)) {
            $ComponentsListStatus = \FILE\read_json_file($path_components_list_status);
        } else {
            $ComponentsListStatus = [];
        }
        
        // Merge components status
        foreach ($ComponentsList as $ki => $ci) {
            // Update status of components with a previous cached status
            $isStatusDefined = FALSE;
            foreach ($ComponentsListStatus as $kr => $cr) {
                if ($ci["component_type"] == $cr["component_type"] && $ci["component_id"] == $cr["component_id"]) {
                    $ComponentsListStatus[$kr]["component_status"] = $ci["component_status"];
                    $isStatusDefined = TRUE;

                    break;
                }
            }
            // Create a status for components without a previous cached status
            if (!$isStatusDefined) {
                $ComponentsListStatus[] = [
                    "component_type" => $ci["component_type"],
                    "component_id" => $ci["component_id"],
                    "component_status" => $ci["component_status"],
                ];
            }
            
            // Clear the status of the main components lists
            unset($ComponentsList[$ki]["component_status"]);
        }
        
        \FILE\write_json_file($path_components_list, $ComponentsList);
        \FILE\write_json_file($path_components_list_status, $ComponentsListStatus);
    }
    
    /**
     * Refresh the components list if needed, or forced by the caller
     *
     * @param bool $force Forces the file overwriting if it already exists (optional, FALSE by default)
     */
    function refreshComponentsListCache($force = FALSE)
    {
        $path_components_list = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE, "components.json"]);
        $path_components_list_status = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA, "components.json"]);
        
        $cachedComponentsListStatusExists = file_exists($path_components_list_status);
        
        if ($force || !file_exists($path_components_list) || !$cachedComponentsListStatusExists) {
            $ComponentsList = checkComponentsList();
            
            if ($cachedComponentsListStatusExists) {
                $ComponentsListStatus = \FILE\read_json_file($path_components_list_status);
                    
                foreach ($ComponentsList as $ki => $ci) {
                    // Search installed components with a cached status
                    $isStatusDefined = FALSE;
                    foreach ($ComponentsListStatus as $kr => $cr) {
                        if ($ci["component_type"] == $cr["component_type"] && $ci["component_id"] == $cr["component_id"]) {
                            $ComponentsList[$ki]["component_status"] = $cr["component_status"];
                            $isStatusDefined = TRUE;
            
                            break;
                        }
                    }
                    // Search installed components without a cached status
                    if (!$isStatusDefined) {
                        $ComponentsList[$ki]["component_status"] = "disabled";
                    }
                }
            }
            
            writeComponentsList($ComponentsList);
        }
    }
    
    /**
     * Return a structured list of installed components
     *
     * The list is extracted form the component.json file of each component and an integrity check
     * is performed against the component. A default activation status is computed from this minimal
     * integrity check and the configuration read.
     *
     * @return array
     */
    function checkComponentsList()
    {
        $componentlist = [];
        $componentPath = [
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS])),
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES])),
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES])),
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR])),
        ];
        
        foreach ($componentPath as $rootpath => $paths) {
            foreach ($paths as $path) {
                $componentDirPath = $rootpath . DIRECTORY_SEPARATOR . $path;
                $component_integrity = checkComponentIntegrity($componentDirPath);
                
                if ($component_integrity > 8 || $component_integrity == 0) {
                    $componentMetadata = \FILE\read_json_file($componentDirPath . DIRECTORY_SEPARATOR . 'component.json');
                } else {
                    $legal_types = [
                        implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]) => "plugin",
                        implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]) => "style",
                        implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]) => "template",
                        implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]) => "vendor",
                    ];
                    $fakejson = '{
                        "component_type": "' . $legal_types[$rootpath] . '",
                        "component_id": "' . $path . '",
                        "component_version": "Unknow",
                        "component_builtin": "false",
                        "component_updatable": "false",
                        "component_name": "' . $path . '",
                        "component_description": "",
                        "component_' . WIKINDX_PACKAGE_HASH_ALGO . '": ""
                    }';
                    $componentMetadata = json_decode($fakejson, TRUE);
                }
                
                $componentMetadata["component_integrity"] = $component_integrity;
                
                // The built-in components are exception. There must always be active for the software to work.
                if ($componentMetadata["component_builtin"] == "true") {
                    $componentMetadata["component_status"] = "enabled";
                }
                // vendor components are alway enabled because their are required by the core
                elseif ($componentMetadata["component_type"] == "vendor") {
                    $componentMetadata["component_status"] = "enabled";
                }
                // All components are disabled by default to minimize errors and unwanted code execution
                else {
                    $componentMetadata["component_status"] = "disabled";
                }
                $componentlist[] = $componentMetadata;
            }
        }
        
        return $componentlist;
    }
    
    
    /**
     * Create the component.json file of a component
     *
     * Do not overwrite the file if it already exists.
     *
     * @param string $component_type (plugin, style, template, or vendor)
     * @param string $component_id
     */
    function createComponentMetadataFile($component_type, $component_id)
    {
        $legal_types = [
            "plugin" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]),
            "style" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]),
            "template" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]),
            "vendor" => implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]),
        ];
        
        $path = implode(DIRECTORY_SEPARATOR, [$legal_types[$component_type], $component_id, 'component.json']);
        
        // Don't overwrite the file
    	if (file_exists($path)) {
    		return;
    	}
    	
        $fakejson = '{
            "component_type": "' . $component_type . '",
            "component_id": "' . $component_id . '",
            "component_version": "0",
            "component_builtin": "false",
            "component_updatable": "false",
            "component_name": "' . $component_id . '",
            "component_description": "' . $component_id . ' ' . $component_type . '",
            "component_' . WIKINDX_PACKAGE_HASH_ALGO . '": ""
        }';
        $componentMetadata = json_decode($fakejson, TRUE);
        
        \FILE\write_json_file($path, $componentMetadata);
    }
    
    
    /**
     * Enable a component
     *
     * @param string $component_type (plugin, style, template, or vendor)
     * @param string $component_id
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    function enableComponent($component_type, $component_id)
    {
        $result = FALSE;
        
        $componentsInstalled = \UTILS\readComponentsList(TRUE);
        
        foreach ($componentsInstalled as $ki => $ci) {
            if ($ci["component_type"] == $component_type && $ci["component_id"] == $component_id) {
                $componentsInstalled[$ki]["component_status"] = "enabled";
                \UTILS\writeComponentsList($componentsInstalled);
                
                $result = TRUE;
                break;
            }
        }
        
        return $result;
    }
    
    
    /**
     * Disable a component
     *
     * @param string $component_type (plugin, style, template, or vendor)
     * @param string $component_id
     *
     * @return bool TRUE on success, FALSE otherwise
     */
    function disableComponent($component_type, $component_id)
    {
        $result = FALSE;
        
        $componentsInstalled = \UTILS\readComponentsList(TRUE);
        
        foreach ($componentsInstalled as $ki => $ci) {
            if ($ci["component_type"] == $component_type && $ci["component_id"] == $component_id) {
                $componentsInstalled[$ki]["component_status"] = "disabled";
                \UTILS\writeComponentsList($componentsInstalled);
                
                $result = TRUE;
                break;
            }
        }
        
        return $result;
    }
    
    
    
    /**
     * Check the integrity of a component
     *
     * @param string $componentDirPath A path to the folder of a component
     *
     * @return int Error code: 0 is for OK and others issues.
     */
    function checkComponentIntegrity($componentDirPath)
    {
        $componentMetadata = \FILE\read_json_file($componentDirPath . DIRECTORY_SEPARATOR . 'component.json');
        if ($componentMetadata !== NULL) {
            // Check mandatory metadata
            $mandatory_properties = ["component_type", "component_id", "component_builtin", "component_updatable", "component_name"];
            foreach ($mandatory_properties as $k) {
                if (!array_key_exists($k, $componentMetadata)) {
                    return 2;
                }
            }
            
            // Check the component id (1)
            $basedir = basename($componentDirPath);
            if ($componentMetadata["component_id"] != $basedir) {
                return 3;
            }
            
            // Check the component id (2)
            if ($componentMetadata["component_id"] != mb_strtolower($componentMetadata["component_id"]) || $basedir != mb_strtolower($basedir)) {
                return 4;
            }
            
            // Check the component type
            $legal_types = ["language", "plugin", "style", "template", "vendor"];
            if (!in_array($componentMetadata["component_type"], $legal_types)) {
                return 5;
            }
            
            // Check if the component type is right directory for the component directory
            $componentRootName = realpath(dirname($componentDirPath));
            if ($componentMetadata["component_type"] == "plugin" && $componentRootName != implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS])) {
                return 6;
            } elseif ($componentMetadata["component_type"] == "style" && $componentRootName != implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES])) {
                return 6;
            } elseif ($componentMetadata["component_type"] == "template" && $componentRootName != implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES])) {
                return 6;
            } elseif ($componentMetadata["component_type"] == "vendor" && $componentRootName != implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR])) {
                return 6;
            }
            
            // Check the component_builtin property
            if (!in_array($componentMetadata["component_builtin"], ["false", "true"])) {
                return 7;
            }
            
            // Check the component_updatable property
            if (!in_array($componentMetadata["component_updatable"], ["false", "true"])) {
                return 8;
            }
            
            // Check the files specific to a language component
            // SRC is not a standard language component and is used only by translators and developers
            if ($componentMetadata["component_type"] == "language" && basename($componentDirPath) != "src") {
                $lcmsgdir = $componentDirPath . DIRECTORY_SEPARATOR . "LC_MESSAGES";
                if (!file_exists($lcmsgdir)) {
                    return 9;
                } elseif (!is_readable($lcmsgdir)) {
                    return 10;
                } elseif (!is_dir($lcmsgdir)) {
                    return 11;
                }
                
                $numMoFiles = 0;
                foreach (\FILE\fileInDirToArray($lcmsgdir) as $file) {
                    if (\UTILS\matchSuffix($file, ".mo")) {
                        $numMoFiles++;

                        break;
                    }
                }
                if ($numMoFiles == 0) {
                    return 12;
                }
            }
            
            // Check the files specific to a plugin component
            if ($componentMetadata["component_type"] == "plugin") {
                $configfile = $componentDirPath . DIRECTORY_SEPARATOR . 'config.php';
                if (!file_exists($configfile)) {
                    return 13;
                } elseif (!is_readable($configfile)) {
                    return 14;
                } elseif (!is_file($configfile)) {
                    return 15;
                }
                
                $indexfile = $componentDirPath . DIRECTORY_SEPARATOR . 'index.php';
                if (!file_exists($indexfile)) {
                    return 16;
                } elseif (!is_readable($indexfile)) {
                    return 17;
                } elseif (!is_file($indexfile)) {
                    return 18;
                }
                
                $plugintypefile = $componentDirPath . DIRECTORY_SEPARATOR . 'plugintype.txt';
                if (!file_exists($plugintypefile)) {
                    return 28;
                } elseif (!is_readable($plugintypefile)) {
                    return 29;
                } elseif (!is_file($plugintypefile)) {
                    return 30;
                }
                
                include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "startup", "LOADPLUGINS.php"]));
                $loadmodules = new \LOADPLUGINS();
                if (!$loadmodules->checkVersion($componentMetadata["component_id"])) {
                    return 25;
                }
            }
            
            // Check the files specific to a style component
            if ($componentMetadata["component_type"] == "style") {
                $xmlstylefile = $componentDirPath . DIRECTORY_SEPARATOR . $componentMetadata["component_id"] . '.xml';
                if (!file_exists($xmlstylefile)) {
                    return 19;
                } elseif (!is_readable($xmlstylefile)) {
                    return 20;
                } elseif (!is_file($xmlstylefile)) {
                    return 21;
                } else {
                    $arrayStyleInfo = \LOADSTYLE\loadStyleInfo($xmlstylefile);
                    
                    if (intval($arrayStyleInfo['osbibversion']) !== WIKINDX_COMPONENTS_COMPATIBLE_VERSION[$componentMetadata["component_type"]]) {
                        return 25;
                    } elseif ($arrayStyleInfo['name'] === NULL) {
                        return 26;
                    } elseif ($arrayStyleInfo['description'] === NULL) {
                        return 27;
                    }
                }
            }
            
            // Check the files specific to a template component
            if ($componentMetadata["component_type"] == "template") {
                $displayfile = $componentDirPath . DIRECTORY_SEPARATOR . 'display.tpl';
                $compatible_versionfile = $componentDirPath . DIRECTORY_SEPARATOR . 'compatible_version';
                if (!file_exists($displayfile)) {
                    return 22;
                } elseif (!is_readable($displayfile)) {
                    return 23;
                } elseif (!is_file($displayfile)) {
                    return 24;
                } else if (!file_exists($compatible_versionfile)) {
                    return 31;
                } elseif (!is_readable($compatible_versionfile)) {
                    return 32;
                } elseif (!is_file($compatible_versionfile)) {
                    return 33;
                } elseif (intval(file_get_contents($compatible_versionfile)) !== WIKINDX_COMPONENTS_COMPATIBLE_VERSION[$componentMetadata["component_type"]]) {
                    return 25;
                }
            }
            
            // Check the files specific to a vendor component
            if ($componentMetadata["component_type"] == "vendor") {
                // Nothing more required for this type
            }
            
            // At this point, we know that the component is apparently healthy for the essential part
            return 0;
        }

        return 1;
    }
    
    /**
     * Return the message of an error code of the function checkComponentIntegrity()
     *
     * @param int $error_code
     *
     * @return string An error message
     */
    function componentIntegrityErrorMessage($error_code)
    {
        $mandatory_properties = ["component_type", "component_id", "component_builtin", "component_updatable", "component_name"];
        $legal_types = ["language", "plugin", "style", "template", "vendor"];
        $msg = [
            0 => "OK",
            1 => "The component.json file is missing, unreadable or not decodable.",
            2 => "A mandatory property (" . implode(",", $mandatory_properties) . ") is missing in the component.json file.",
            3 => "The value of the 'component_id' is not equal to the folder name of the component.",
            4 => "The 'component_id' and the folder name of the component must be in lowercase.",
            5 => "The value of the 'component_type' property is invalid (legal values: " . implode(",", $legal_types) . ").",
            6 => "The 'component_type' property is note valid or this component is installed in the wrong folder.",
            7 => "The value of the 'component_builtin' is invalid (legal values: false, true).",
            8 => "The value of the 'component_updatable' is invalid (legal values: false, true).",
            9 => "The LC_MESSAGES subdirectory is missing.",
            10 => "The LC_MESSAGES subdirectory is not readable.",
            11 => "LC_MESSAGES is not a directory.",
            12 => "The LC_MESSAGES subdirectory does not contain any gettext compiled catalogs (MO files).",
            13 => "The config.php file is missing.",
            14 => "The config.php file is not readable.",
            15 => "config.php is not a file.",
            16 => "The index.php file is missing.",
            17 => "The index.php file is not readable.",
            18 => "index.php is not a file.",
            19 => "The bibliographic XML style file is missing or is not named in uppercase (stylename.xml).",
            20 => "The bibliographic XML style file is not readable.",
            21 => "The bibliographic XML style file is not a file.",
            22 => "The display.tpl file is missing.",
            23 => "The display.tpl file is not readable.",
            24 => "display.tpl is not a file.",
            25 => "The installed version of this component is not compatible with the version required by this version of WIKINDX.",
            26 => "The internal component name is missing.",
            27 => "The internal component description is missing.",
            28 => "The plugintype.txt file is missing.",
            29 => "The plugintype.txt file is not readable.",
            30 => "plugintype.txt is not a file.",
            31 => "The compatible_version file is missing.",
            32 => "The compatible_version file is not readable.",
            33 => "compatible_version is not a file.",
        ];

        return array_key_exists($error_code, $msg) ? $msg[$error_code] : "Unknow";
    }
    
    /**
     * Generates a hash of a file or directory at the given path with the specified algo.
     * If the path is to a single file, it uses sha1_file. Otherwise, it
     * recursively loops through all files in a directory to generate the hash.
     *
     * @see https://www.php.net/manual/en/function.hash-algos.php
     *
     * @param string $path - the path of the folder or file
     * @param string $algo - the hash algo used (WIKINDX_PACKAGE_HASH_ALGO by default)
     *
     * @return string
     */
    function hash_path($path, $algo = WIKINDX_PACKAGE_HASH_ALGO)
    {
        // just sha1_file for regular files
        if (!is_dir($path)) {
            return hash_file($algo, $path);
        }
        
        $hashes = []; // an array to store the hash values
        
        //$dir = dir($path);
        // loop through the directory, recusively building an array
        // of all the hashes contained within
        foreach (\FILE\dirToArray($path) as $file) {
            //while (FALSE !== ($file = $dir->read()))
            // make sure we don't use . or ..
            if (!in_array($file, ['.', '..'])) {
                // get the hash of this path and add it to our array
                $hashes[] = hash_path($path . DIRECTORY_SEPARATOR . $file);
            }
        }
        
        // combine all the hashes, then hash that for the final value
        return hash($algo, implode('-', $hashes));
    }
    
    /**
     * Download a file from SourceForge with curl
     *
     * Curl is configured to track SF redirects to the nearest server.
     *
     * @param string $url - the url of a resource on SF
     * @param string $file - the path of a file on the server
     *
     * @return bool TRUE if the download is successful, FALSE otherwise
     */
    function download_sf_file($url, $file)
    {
        $fp = fopen($file, 'wb');
        $ch = curl_init();
        $headers = [];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 32);
        
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        curl_setopt($ch, CURLOPT_FILE, $fp);
        
        $ccode = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        
        // On error remove the file
        if ($ccode === FALSE) {
            @unlink($file);
        }
        
        return ($ccode !== FALSE);
    }
    
    /*
    * Return a (pseudo) unique string of variable length using random_bytes().
    *
    * @params int $length length of the returned string (Default is 16).
    * @return string
    */
    function uuid(int $length = 16)
    {
        $str = "";
        while (strlen($str) < $length) {
            $str .= bin2hex(random_bytes($length));
        }

        return substr($str, 0, $length);
    }
    
    /**
     * Check the permissions of various folders and files which must be writable
     */
    function checkFoldersPerms()
    {
        // No verification on Windows which does not have an Unix permissions system
        // because IIS would be the only one to use the native permissions of this system
        // and it is not officially supported
        if (\UTILS\OSName() == "windows") {
            return;
        }
        
        $aErrorPerms = [];
        
        $folderstocheck = [
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA])),
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_CACHE])),
            
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_PLUGINS])),
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES])),
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_TEMPLATES])),
            implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR]) => \FILE\dirInDirToArray(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_VENDOR])),
            
        ];
        
        foreach ($folderstocheck as $root => $paths) {
            foreach ($paths as $path) {
                $dir = $root . DIRECTORY_SEPARATOR . $path;
                
                if (!is_readable($dir)) {
                    $aErrorPerms[$dir] = "r";
                }
                if (!is_writable($dir)) {
                    if (array_key_exists($dir, $aErrorPerms)) {
                        $aErrorPerms[$dir] .= "w";
                    } else {
                        $aErrorPerms[$dir] = "w";
                    }
                }
            }
        }
        
        if (count($aErrorPerms) > 0) {
            $string = "<table>";
            $string .= "<tr>";
            $string .= "<th>Folder</th>";
            $string .= "<th>Current Unix perms</th>";
            $string .= "<th>Missing perms</th>";
            $string .= "</tr>";
            foreach ($aErrorPerms as $name => $perm) {
                $string .= "<tr>";
                $string .= "<td>" . $name . "</td>";
                $string .= "<td>" . substr(sprintf('%o', fileperms($name)), -4) . "</td>";
                $string .= "<td>" . $perm . "</td>";
                $string .= "</tr>";
            }
            $string .= "</table>";
            
            die("WIKINDX will not function correctly if various folders and files within them are not writeable for the web server user.
            The following folders, shown with their current Unix permissions, should be made readable and writeable (along with their contents) for 
            the web server user. The web server user can be the owner and/or the group of those folders. So you have to modify, the owner, the group and the permission bits according to the particular configuration of your web server, PHP and file transfer software. You may also be required to add the execution bit in certain cases. The same rights apply to files in these folders, but this script does not check them for performance reasons. See the chmod, web server and PHP manuals, and docs/INSTALL.txt for details.<p><p>r = readable; w = writable ; x = executable</p>" . $string);
        }
    }
    
    /**
     * Take input from HTML FORM <input type=date> and split into separate fields.
     * Date comes in as 'yyyy-mm-dd' (but displayed on web form as 'dd / mm / yyyy').
     * All three fields must have a valid value else the form input is FALSE. This should be tested before calling this function.
     *
     * @param string $dateInput
     *
     * @return array array(year, month, day)
     */
    function splitDate($dateInput)
    {
        $date = \UTF8\mb_explode('-', $dateInput);

        return [$date[0], $date[1], $date[2]];
    }
    
    /*
     * Creates a password hash
     *
     * This is a simplified version of the eponymous PHP function.
     * It hides the implementation changes linked to the evolution of cryptographic techniques.
     *
     * Implementation history
     *
     * - v1 (before 5.2.0):    use crypt() with a salt generated by: strrev(time()).
     * - v2 (5.2.0 to 6.3.10): use crypt() with a salt generated by: \UTF8\mb_strrev(time()).
     * - v3 (from 6.4.0):      use crypt() with a salt generated by: strrev(time()).
     * - v4 (from 6.4.1):      use password_hash() with PASSWORD_DEFAULT algo without custom options.
     *
     * The last version replaced the pre-PHP 5.5 crypt() function by password_hash()
     * which has stronger defaults and is protected against time attacks.
     *
     * @version 3
     *
     * @todo Insert a pepper hash before PHP hash from a pepper string defined in config.php
     * which cannot be compromised by a database leak.
     *
     * @param string $password A clear password to encrypt/hash
     *
     * @return bool
     */
    function password_hash($password)
    {
        //return \password_hash($password, PASSWORD_DEFAULT);
        return crypt($password, strrev(time()));
    }
    
    /*
     * Verifies that a password matches a hash
     *
     * This is a simplified version of the eponymous PHP function.
     * It hides the implementation changes linked to the evolution of cryptographic techniques.
     * cf. https://www.php.net/manual/en/function.password-hash.php#124138
     *
     * Implementation history: see \UTILS\password_hash()
     *
     * @param string $password A clear password to verify
     * @param string $password_hashed A hash to compare created with \UTILS\password_hash()
     *
     * @return bool
     */
    function password_verify($password, $password_hashed)
    {
        //return \password_verify($password, $password_hashed);
        return crypt($password, $password_hashed) == $password_hashed;
    }
}
