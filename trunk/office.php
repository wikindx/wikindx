<?php
// Import initial configuration and initialize the web server
include_once("core/startup/WEBSERVERCONFIG.php");
$class = new OFFICE();
$class->init();

class OFFICE
{
    private $db;
    private $vars;
    private $bibStyle;
    private $citeStyle;
    private $search;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = FACTORY_DB::getInstance();
        $this->vars = GLOBALS::getVars();
        $this->bibStyle = FACTORY_BIBSTYLE::getInstance(); // HTML
        $this->bibStyle->ooxml = TRUE;
        $this->citeStyle = FACTORY_CITESTYLE::getInstance(); // HTML
        include_once(implode(DIRECTORY_SEPARATOR, ["core", "modules", "office", "SEARCH.php"]));
        $this->search = new SEARCH();
		header('Access-Control-Allow-Origin: *');
    }
    /**
     * What do we do?
	 */
    public function init()
    {
    	if (array_key_exists('method', $this->vars)) {
    		switch ($this->vars['method']) {
    			case 'getCitation':
    				$this->getCitations();
    				break;
    			case 'getStyles':
    				$this->getStyles();
    				break;
    			default:
    				break;
    			break;
    		}
    	}
    }
    /**
     * Echo the citation
     *
     */
    private function getCitations()
    {
    	$id = array_key_exists('searchWord', $this->vars) ? $this->vars['searchWord'] : 1119; // for test purposes . . .
    	$this->search->input['Word'] = \UTF8\mb_trim($this->vars['searchWord']);
    	$sql = $this->search->process();
    	$json = $this->formatResults($sql);
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
    	$jsonArray = [];
    	$resultSet = $this->db->query($sql);
    	while ($row = $this->db->fetchRow($resultSet)) {
    		$bibEntry = $this->bibStyle->process($row);
    		$citation = trim($this->citeStyle->start('[cite]' . $row['resourceId'] . '[/cite]', FALSE));
    		$jsonArray[] = ['id' => $row['resourceId'], 'bibEntry' => $bibEntry, 'citation' => $citation];
    	}
    	return json_encode($jsonArray);
    }
    /*
     * Return a list of available bibliographic styles
     */
    private function getStyles()
    {
    	echo json_encode(\LOADSTYLE\loadDir());
    }
}
?>