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
    private $output = 'html'; // default
    private $stripTags = FALSE;
    
    /**
     * Constructor
     */
    public function __construct()
    {
/* Use for debugging . . .
GLOBALS::startPageTimer();
GLOBALS::stopPageTimer();
$scriptExecutionTimeAfterRendering = GLOBALS::getPageElapsedTime();
echo $scriptExecutionTimeAfterRendering . 's';die;
*/
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
    	if ($this->vars['source'] == 'googleDocs') {
    		$this->output = 'noScan';
    		$this->stripTags = TRUE;
    	}
    	if (array_key_exists('method', $this->vars)) {
    		switch ($this->vars['method']) {
    			case 'getReferences':
    				$this->getReferences();
    				break;
    			case 'getReference':
    				$this->getReference();
    				break;
    			case 'getCitations':
    				$this->getCitations();
    				break;
    			case 'getCitation':
    				$this->getCitation();
    				break;
    			case 'getBib':
    				$this->getBib();
    				break;
    			case 'getCiteCCs':
    				$this->getCiteCCs();
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
    	if (WIKINDX_DENY_READONLY) { // not allowed!
    		echo json_encode("access denied");
    		die;
    	}
    	echo json_encode("It's alive!");
    	die;
    }
    /**
     * Get the finalized bibliography
     *
     */
    private function getBib()
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        include_once(implode(DIRECTORY_SEPARATOR, ["core", "modules", "office", "SEARCH.php"]));
        $search = new SEARCH();
    	$split = explode('_', $this->vars['searchParams']);
    	$search->order = $split[0];
    	$search->ascDesc = $split[1];
    	$search->ids = json_decode($this->vars['ids']);
    	$sql = $search->getFinalSqlResources();
    	$json = $this->formatResultsReferences($sql, FALSE);
    	echo $json;
    	die;
    }
    /**
     * Get in-text citations for citations (possibly including page numbers . . .)
     *
     */
    private function getCiteCCs()
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        $bibStyle = FACTORY_BIBSTYLE::getInstance(); // HTML
        $bibStyle->ooxml = TRUE;
        $citeStyle = FACTORY_CITESTYLE::getInstance(); // HTML
        $citeStyle->ooxml = TRUE;
    	$jsonArray = [];
    	$this->db->formatConditionsOneField(json_decode($this->vars['ids']), 'resourcemetadataId');
    	$this->db->leftJoin('resource', 'resourceId', 'resourcemetadataResourceId');
    	$this->db->leftJoin('resource_text', 'resourcetextId', 'resourcemetadataResourceId');
    	$this->db->leftJoin('resource_year', 'resourceyearId', 'resourcemetadataResourceId');
    	$resultSet = $this->db->select('resource_metadata', ['resourcemetadataPageStart', 'resourcemetadataPageEnd', 
    		'resourcemetadataResourceId', 'resourcemetadataId', 'resourceType', 'resourceTitle', 'resourceSubtitle', 
    		'resourceShortTitle', 'resourceNoSort', 'resourcetextUrls', 'resourceyearYear1', 'resourceyearYear2', 'resourceyearYear3']);
    	if (!$this->db->numRows($resultSet)) {
    		echo json_encode("Bad ID");
    		die;
    	}
    	while ($row = $this->db->fetchRow($resultSet)) {
			$pageStart = $row['resourcemetadataPageStart'] ? $row['resourcemetadataPageStart'] : FALSE;
			$pageEnd = $row['resourcemetadataPageEnd'] ? $row['resourcemetadataPageEnd'] : FALSE;
			$reference = trim($citeStyle->startOoxml($row['resourcemetadataResourceId'], $pageStart, $pageEnd, $row));
			if ($this->vars['source'] == 'googleDocs') {
				$reference = html_entity_decode($reference, ENT_QUOTES, 'UTF-8');
			}
    		$jsonArray[] = ['metaId' => $row['resourcemetadataId'], 'inTextReference' => $reference, 'id' => $row['resourcemetadataResourceId']];
    	}
    	echo json_encode($jsonArray);
    	die;
    }
    /**
     * Get the reference list from the search
     *
     */
    private function getReferences()
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        include_once(implode(DIRECTORY_SEPARATOR, ["core", "modules", "office", "SEARCH.php"]));
        $search = new SEARCH();
    	$search->input['Word'] = \UTF8\mb_trim($this->vars['searchWord']);
    	$split = explode('_', $this->vars['searchParams']);
    	$search->order = $split[0];
    	$search->ascDesc = $split[1];
    	$sql = $search->processResources();
    	$json = $this->formatResultsReferences($sql);
    	echo $json;
    	die;
    }
    /**
     * Get single reference from a resource id
     *
     */
    private function getReference()
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        $res = FACTORY_RESOURCECOMMON::getInstance();
        $bibStyle = FACTORY_BIBSTYLE::getInstance(); // HTML
        $bibStyle->ooxml = TRUE;
        $citeStyle = FACTORY_CITESTYLE::getInstance(); // HTML
        $citeStyle->ooxml = TRUE;
    	$resultSet = $res->getResource($this->vars['id']);
    	if (!$this->db->numRows($resultSet)) {
    		echo json_encode("Bad ID");
    		die;
    	}
    	$row = $this->db->fetchRow($resultSet);
    	$bibEntry = $bibStyle->process($row);
    	$reference = trim($citeStyle->startOoxml($this->vars['id'], FALSE, FALSE, $row));
    	$titleString = html_entity_decode(strip_tags($bibStyle->titleString));
    	$titleString = str_replace(['{', '}'], '', $titleString);
    	$jsonArray = ['id' => $row['resourceId'], 'bibEntry' => $bibEntry, 'inTextReference' => $reference, 'titleCC' => $titleString];
    	$json = json_encode($jsonArray);
    	echo $json;
    	die;
    }
    /**
     * Get single citation from a resourcemetadata id
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
    	$this->db->formatConditions(['resourcemetadataId' => $this->vars['id']]);
    	$resultSet = $this->db->select('resource_metadata', ['resourcemetadataText', 'resourcemetadataPageStart', 'resourcemetadataPageEnd', 
    		'resourcemetadataResourceId']);
    	if (!$this->db->numRows($resultSet)) {
    		echo json_encode("Bad ID");
    		die;
    	}
    	$row = $this->db->fetchRow($resultSet);
    	$citation = $row['resourcemetadataText'];
    	// Remove all images
    	$citation = preg_replace("/<img[^>]+\>/i", "(image removed)", $citation);
    	// Remove all in-text citations – maybe deal with at a later date
    	$citation = preg_replace("/(\\[cite])(.*)(\\[\\/cite\\])/Uus", '(citation removed)', $citation);
		$pageStart = $row['resourcemetadataPageStart'] ? $row['resourcemetadataPageStart'] : FALSE;
		$pageEnd = $row['resourcemetadataPageEnd'] ? $row['resourcemetadataPageEnd'] : FALSE;
    	$resultSet = $res->getResource($row['resourcemetadataResourceId']);
    	if (!$this->db->numRows($resultSet)) {
    		echo json_encode("Bad ID");
    		die;
    	}
    	$row = $this->db->fetchRow($resultSet);
    	$bibEntry = $bibStyle->process($row);
    	$reference = trim($citeStyle->startOoxml($row['resourceId'], $pageStart, $pageEnd, $row));
    	$titleString = html_entity_decode(strip_tags($bibStyle->titleString));
    	$titleString = str_replace(['{', '}'], '', $titleString);
    	if (!$this->vars['withHtml']) {
    		$citation = strip_tags($citation);
    		if ($this->vars['source'] == 'googleDocs') {
    			$citation = html_entity_decode($citation, ENT_QUOTES, 'UTF-8');
    			$reference = html_entity_decode($reference, ENT_QUOTES, 'UTF-8');
    		}
    	}
    	$jsonArray = ['id' => $row['resourceId'], 'bibEntry' => $bibEntry, 'inTextReference' => $reference, 
    		'citation' => $citation, 'titleCC' => $titleString, 'metaId' => $this->vars['id']];
    	$json = json_encode($jsonArray);
    	echo $json;
    	die;
    }
    /**
     * Format and return the reference search results
     *
     * @param string $sql
     * @param bool $short = TRUE
     */
    private function formatResultsReferences($sql, $short = TRUE)
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        $bibStyle = FACTORY_BIBSTYLE::getInstance(); // HTML
        $bibStyle->ooxml = TRUE;
        $citeStyle = FACTORY_CITESTYLE::getInstance(); // HTML
        $citeStyle->ooxml = TRUE;
    	$jsonArray = [];
    	$resultSet = $this->db->query($sql);
    	while ($row = $this->db->fetchRow($resultSet)) {
    		if ($short) {
	    		$bibEntry = strip_tags($bibStyle->process($row));
	    	} else {
    			$bibEntry = $bibStyle->process($row); 
    		}
    		if ($short && (mb_strlen($bibEntry) > 69)) {// For the add-in select box which has c. 70 chars/option
	    		$bibEntry = mb_substr($bibEntry, 0, 70);
	    	}
    		$reference = trim($citeStyle->startOoxml($row['resourceId'], FALSE, FALSE, $row));
    		if ($short) {
	    		$jsonArray[] = ['id' => $row['resourceId'], 'bibEntry' => $bibEntry, 'inTextReference' => $reference];
    		} else {
    			$tos = html_entity_decode(strip_tags($bibStyle->titleOrderString));
    			$tos = str_replace(['{', '}'], '', $tos);
    			if (!$bibStyle->creatorOrderString) {
        			$bibStyle->creatorOrderString = $tos; // default for ordering in ooxml javascript with no creators
        		}
				if ($this->vars['source'] == 'googleDocs') {
					$bibEntry = html_entity_decode($bibEntry, ENT_QUOTES, 'UTF-8');
					$reference = html_entity_decode($reference, ENT_QUOTES, 'UTF-8');
				}
	    		$jsonArray[] = ['id' => $row['resourceId'], 'bibEntry' => $bibEntry, 'inTextReference' => $reference, 
    				'creatorOrder' =>  html_entity_decode($bibStyle->creatorOrderString), 'titleOrder' => $tos, 
    				'yearOrder' => $bibStyle->yearOrderString];
    		}
    		$bibStyle->creatorOrderString = ''; // Reset concatenation string
    	}
    	return json_encode($jsonArray);
    }
    /**
     * Format and return the citation search results
     *
     * @param string $sql
     */
    private function formatResultsCitations($sql)
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        $bibStyle = FACTORY_BIBSTYLE::getInstance(); // HTML
        $bibStyle->ooxml = TRUE;
    	$jsonArray = [];
    	$resultSet = $this->db->query($sql);
    	while ($row = $this->db->fetchRow($resultSet)) {
			$citation = strip_tags($row['resourcemetadataText']);
    		if (mb_strlen($citation) > 69) {// For the add-in select box which has c. 70 chars/option
	    		$citation = mb_substr($citation, 0, 70);
	    	}
    		// Remove all in-text citations – maybe deal with at a later date
    		$citation = preg_replace("/(\\[cite])(.*)(\\[\\/cite\\])/Uus", '', $citation);
    		$jsonArray[] = ['id' => $row['resourcemetadataId'], 'citation' => $citation];
    	}
    	return json_encode($jsonArray);
    }
    
    /**
     * Get the citation list from the search
     *
     */
    private function getCitations()
    {
    	GLOBALS::setUserVar("Style", $this->vars['style']);
        include_once(implode(DIRECTORY_SEPARATOR, ["core", "modules", "office", "SEARCH.php"]));
        $search = new SEARCH();
    	$search->input['Word'] = \UTF8\mb_trim($this->vars['searchWord']);
    	$split = explode('_', $this->vars['searchParams']);
    	$search->order = $split[0];
    	$search->ascDesc = $split[1];
    	$sql = $search->processCitations();
    	$json = $this->formatResultsCitations($sql);
    	echo $json;
    	die;
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
