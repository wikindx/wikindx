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
 * Import initial configuration and initialize the web server
 */
include_once("core/startup/WEBSERVERCONFIG.php");

$class = new OFFICE();
$class->init();

class OFFICE
{
    private $db;
    private $vars;
    
    /**
     * Constructor
     */
    public function __construct()
    {
		GLOBALS::deleteUserVarsArray();
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
		header('Access-Control-Allow-Origin: *');
    }
    /**
     * What do we do?
	 */
    public function init()
    {
    	if (array_key_exists('method', $this->vars)) {
    		switch ($this->vars['method']) {
    			case 'getResources':
    				$this->getResources();
    				break;
    			case 'getCitation':
    				$this->getCitation();
    				break;
    			case 'getStyles':
    				$this->getStyles();
    				break;
    			case 'heartbeat':
    				$this->heartbeat();
    				break;
    			default:
    				break;
    			break;
    		}
    	}
    }
    /**
     * Provide a heartbeat to check a user-entered URL in the add-in is reaching here correctly
     *
     */
    private function heartbeat()
    {
    	echo json_encode("It's alive!");
    }
    /**
     * Get the resource list from the search
     *
     */
    private function getResources()
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        include_once(implode(DIRECTORY_SEPARATOR, ["core", "modules", "office", "SEARCH.php"]));
        $search = new SEARCH();
    	$id = array_key_exists('searchWord', $this->vars) ? $this->vars['searchWord'] : 1119; // for test purposes . . .
    	$search->input['Word'] = \UTF8\mb_trim($this->vars['searchWord']);
    	$split = explode('_', $this->vars['searchParams']);
    	$search->order = $split[0];
    	$search->ascDesc = $split[1];
    	$sql = $search->process();
    	$json = $this->formatResults($sql);
    	echo $json;
    	die;
    }
    /**
     * Get single citation from a resource id
     *
     */
    private function getCitation()
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        $res = FACTORY_RESOURCECOMMON::getInstance();
        $bibStyle = FACTORY_BIBSTYLE::getInstance(); // HTML
        $bibStyle->ooxml = TRUE;
        $citeStyle = FACTORY_CITESTYLE::getInstance(); // HTML
        $citeStyle->ooxml = TRUE;
    	$id = array_key_exists('id', $this->vars) ? $this->vars['id'] : 1119; // for test purposes . . .
    	$resultSet = $res->getResource($id);
    	$row = $this->db->fetchRow($resultSet);
    	$bibEntry = $bibStyle->process($row);
    	$citation = trim($citeStyle->start('[cite]' . $id . '[/cite]', FALSE));
    	$jsonArray = ['id' => $row['resourceId'], 'bibEntry' => $bibEntry, 'citation' => $citation];
    	$json = json_encode($jsonArray);
    	echo $json;
    	die;
    }
    /**
     * Format and return the results
     *
     * @param string $sql
     */
    private function formatResults($sql)
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        $bibStyle = FACTORY_BIBSTYLE::getInstance(); // HTML
        $bibStyle->ooxml = TRUE;
        $citeStyle = FACTORY_CITESTYLE::getInstance(); // HTML
        $citeStyle->ooxml = TRUE;
    	$jsonArray = [];
    	$resultSet = $this->db->query($sql);
    	while ($row = $this->db->fetchRow($resultSet)) {
    		$bibEntry = $bibStyle->process($row);
    		$citation = trim($citeStyle->start('[cite]' . $row['resourceId'] . '[/cite]', FALSE));
    		$jsonArray[] = ['id' => $row['resourceId'], 'bibEntry' => $bibEntry, 'citation' => $citation];
    	}
    	return json_encode($jsonArray);
    }
    /*
     * Return a list of available bibliographic styles
     */
    private function getStyles()
    {
    	$jsonArray = [];
    	foreach (\LOADSTYLE\loadDir() as $short => $long) {
    		if ($this->extractInfo(implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_COMPONENT_STYLES, $short, $short . ".xml"]))) {
	    		$jsonArray[] = ['styleShort' => $short, 'styleLong' => $long];
    		}
    	}
    	echo json_encode($jsonArray);
    	die;
    }
    /**
     * Extract type of style from style file
     *
     * @param string $file - Location of StyleFile
     * @return array
     */
    public function extractInfo($file)
    {
        $xmlString = simplexml_load_file($file);
        $array = $this->XMLToArray($xmlString->citation);
        if ($array['citationStyle']) { // is endote or footnote style
        	return FALSE;
        }
        return TRUE; // intext citation
    }
    /**
     * Convert XML to array
     *
     * code borrowed from http://php.net
     *
     * @param string $xml
     *
     * @return mixed
     */
    private function XMLToArray($xml)
    {
        if ($xml instanceof SimpleXMLElement)
        {
            $children = $xml->children();
            $return = NULL;
        }
        foreach ($children as $element => $value)
        {
            if ($value instanceof SimpleXMLElement)
            {
                $values = (array)$value->children();
                if (count($values) > 0)
                {
                    $return[$element] = $this->XMLToArray($value);
                }
                else
                {
                    if (!isset($return[$element]))
                    {
                        $return[$element] = (string)$value;
                    }
                    else
                    {
                        if (!is_array($return[$element]))
                        {
                            $return[$element] = [$return[$element], (string)$value];
                        }
                        else
                        {
                            $return[$element][] = (string)$value;
                        }
                    }
                }
            }
        }
        if (is_array($return))
        {
            return $return;
        }
        else
        {
            return FALSE;
        }
    }
}
