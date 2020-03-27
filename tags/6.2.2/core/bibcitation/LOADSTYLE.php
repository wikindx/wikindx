<?php
/**
 * WIKINDX : Bibliographic Management system.
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Load the bibliographic style list
 *
 * @package wikindx\core\bibcitation
 */
namespace LOADSTYLE
{
/**
 * LOADSTYLE
 */
const ROOT_DIR = WIKINDX_DIR_COMPONENT_STYLES;
/**
 * OSBIB version information
 *
 * @name OSBIB_VERSION
 */
const OSBIB_VERSION = "3.2";
    /**
     * Read ROOT_DIR directory for XML style files and return an associative array. Each XML file should
     * be within its own folder within ROOT_DIR.  This folder name should match the first part of the XML file name e.g.
     * apa/apa.xml or chicago/chicago.xml.
     *
     * CONSTANTS.php stores OSBIB_VERSION -- the osbibVersion field of the style XML file must equal this.
     *
     * @param $all bool If TRUE, force the loading of all styles
     *
     * @return array Sorted associative array - keys = filename (less '.xml'), values = Style description.
     */
    function loadDir($all = FALSE)
    {
        $array = [];
        
        $componentsInstalled = \UTILS\readComponentsList();
        
        foreach ($componentsInstalled as $cmp)
        {
            if ($cmp["component_type"] == "style" && ($cmp["component_status"] == "enabled" || $all))
            {
                $fileName = $cmp["component_id"] . ".xml";
                $filePath = \LOADSTYLE\ROOT_DIR . DIRECTORY_SEPARATOR . $cmp["component_id"] . DIRECTORY_SEPARATOR . $fileName;
                
                $arrayStyleInfo = \LOADSTYLE\loadStyleInfo($filePath);
                $array[$cmp["component_id"]] = $arrayStyleInfo['description'];
            }
        }
        
        ksort($array);

        return $array;
    }
    /**
     * Extract info entries from a XML bibliographic style file
     * and return an associative array of childnode's value of 'info' node.
     *
     * This function is taylored by hand to replace the generic parser simplexml_load_file.
     * SimpleXml is very simple to use, but is so slow that parse each style file of Wikindx
     * implies a waste of 100 ms in each load.
     *
     * This function is closed to a determined tree node as showed below:
     *
     * <?xml version="1.0" encoding="utf-8"?>
     * <style xml:lang="en">
     *    <info>
     *       <name>IDSTYLE</name>
     *       <description>Identifier of my custom bibliographic Style (IDSTYLE)</description>
     *       <language>English</language>
     *       <osbibVersion>3.2</osbibVersion>
     *    </info>
     *    [...]
     * </style>
     *
     * @param string $file Location of a StyleFile
     *
     * @return associative array - keys = name, description, language, osbibversion
     */
    function loadStyleInfo($file)
    {
        $nodeStyle = NULL;
        $nodeInfo = NULL;
        $nodeName = NULL;
        $nodeDescription = NULL;
        $nodeLanguage = NULL;
        $nodeOsbibVersion = NULL;

        $nodeId = '';

        $pXML = new \XMLReader();

        if ($pXML->open($file))
        {
            while ($pXML->read())
            {
                // Stop parsing when we are at the end of 'info' node
                if ($pXML->nodeType == \XMLReader::END_ELEMENT && mb_strtolower($pXML->name) == 'info')
                {
                    break;
                }
                // When we are on a data that is not a begin of node, we skip it
                elseif ($pXML->nodeType != \XMLReader::ELEMENT)
                {
                    continue;
                }
                // When we are on a data that is a begin of node, we memorize its name
                // and sse if we can explore its childs or get its value
                else
                {
                    $nodeId = mb_strtolower($pXML->name);
                }

                // If we find 'style' root node, keep it in memory, we explore its child nodes now
                if (!$nodeStyle && $nodeId == 'style')
                {
                    $nodeStyle = TRUE;

                    continue;
                }
                else
                {
                    // If we find 'info' node, keep it in memory, we explore its child nodes now
                    if (!$nodeInfo && $nodeId == 'info')
                    {
                        $nodeInfo = TRUE;

                        continue;
                    }
                    else
                    {
                        // Read each value needed
                        if (!$nodeName && $nodeId == 'name')
                        {
                            $pXML->read();
                            $nodeName = $pXML->value;

                            continue;
                        }

                        if (!$nodeDescription && $nodeId == 'description')
                        {
                            $pXML->read();
                            $nodeDescription = $pXML->value;

                            continue;
                        }

                        if (!$nodeLanguage && $nodeId == 'language')
                        {
                            $pXML->read();
                            $nodeLanguage = $pXML->value;

                            continue;
                        }

                        if (!$nodeOsbibVersion && $nodeId == 'osbibversion')
                        {
                            $pXML->read();
                            $nodeOsbibVersion = $pXML->value;

                            continue;
                        }
                    }
                }
            }

            $pXML->close();
        }

        unset($pXML);

        return [
            'name' => $nodeName,
            'description' => $nodeDescription,
            'language' => $nodeLanguage,
            'osbibversion' => $nodeOsbibVersion,
        ];
    }
}
