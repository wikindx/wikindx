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
  * visualize class.
  *
  * Visualize
  *
  * Create various visualizations from WIKINDX data.
  *
  * Makes use of JpGraph: https://jpgraph.net/
  */

/**
 * Import initial configuration and initialize the web server
 */
include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "startup", "WEBSERVERCONFIG.php"]));


class visualize_MODULE
{
    public $authorize;
    public $menus;
    private $pluginmessages;
    private $coremessages;
    private $errors;
    private $config;
    private $session;
    private $vars;
    private $db;
    private $xAxisMetadata;
    private $xAxis = [];
    private $yAxis = [];
    private $colour;

    /**
     * Constructor
     *
     * @param bool $menuInit is TRUE if called from MENU.php
     */
    public function __construct($menuInit = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "..", "core", "messages", "PLUGINMESSAGES.php"]));
        $this->coremessages = FACTORY_MESSAGES::getInstance();
        $this->pluginmessages = new PLUGINMESSAGES('visualize', 'visualizeMessages');
        $this->errors = FACTORY_ERRORS::getInstance();
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "config.php"]));
        $this->config = new visualize_CONFIG();
        $this->session = FACTORY_SESSION::getInstance();
        $this->db = FACTORY_DB::getInstance();
        $this->authorize = $this->config->authorize;
        if ($menuInit)
        {
            $this->makeMenu($this->config->menus);

            return; // Need do nothing more as this is simply menu initialisation.
        }
        $authorize = FACTORY_AUTHORIZE::getInstance();
        if (!$authorize->isPluginExecutionAuthorised($this->authorize))
        { // not authorised
            FACTORY_CLOSENOMENU::getInstance(); // die
        }
        $this->vars = GLOBALS::getVars();
        GLOBALS::setTplVar('heading', $this->pluginmessages->text('heading'));
    }
    /**
     * This is the initial method called from the menu item.
     *
     * Present options
     *
     * @param false|string $message
     */
    public function init($message = FALSE)
    {
        if ($message)
        {
            $pString = $message;
        }
        else
        {
            $pString = '';
        }
        $pString .= FORM\formHeader("visualize_visualize");
        $pString .= HTML\tableStart('generalTable borderStyleSolid');
        $pString .= HTML\trStart();
        $pString .= HTML\td($this->chooseOptions());
        $pString .= HTML\trEnd();
        $pString .= HTML\trStart();
        $js = "onClick=\"visualizePopUp(); return false\"";
        $td = HTML\p(FORM\formSubmit($this->pluginmessages->text('visualize'), FALSE, $js));
        $pString .= HTML\td($td);
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();
        $pString .= FORM\formEnd();
        \AJAX\loadJavascript(WIKINDX_URL_BASE . '/' . WIKINDX_URL_COMPONENT_PLUGINS . '/visualize/visualize.js?ver=' . WIKINDX_PUBLIC_VERSION);
        GLOBALS::addTplVar('content', $pString);
    }
    /**
     * Carry out the visualization
     */
    public function visualize()
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "jpgraph", "jpgraph.php"]));
        if (!$this->validate())
        {
            $this->badInput($this->pluginmessages->text('inputMissing'));
        }
        $this->xAxisMetadata = $this->getXAxisMetadata();
        $this->getData();
        if (array_key_exists('maxXAxis', $this->vars) && ($this->vars['maxXAxis'] > 0))
        {
            $scale = $this->vars['maxXAxis'];
        }
        else
        {
            $scale = FALSE;
        }
        if ($scale && (count($this->xAxis) > $scale))
        {
            if (!array_key_exists('start', $this->vars) || !$this->vars['start'])
            {
                $xAxis = array_slice($this->xAxis, 0, $scale);
                $yAxis = array_slice($this->yAxis, 0, $scale);
            }
            else
            {
                $xAxis = array_slice($this->xAxis, $this->vars['start'], $scale);
                $yAxis = array_slice($this->yAxis, $this->vars['start'], $scale);
            }
        }
        else
        {
            $xAxis = $this->xAxis;
            $yAxis = $this->yAxis;
        }
        // Create a graph instance
        $graph = new Graph($this->config->width, $this->config->height);
        // Specify what scale we want to use
        $scales = $this->xAxisMetadata[$this->vars['xAxis']]['xScale'] . $this->xAxisMetadata[$this->vars['xAxis']]['yScale'];
        $graph->SetScale($scales);
        $graph->SetMargin(80, 20, 60, $this->xAxisMetadata[$this->vars['xAxis']]['xAxisMargin']);
        $graph->SetTickDensity(TICKD_DENSE, TICKD_VERYSPARSE);
        // Setup a title for the graph
        $graph->title->Set($this->pluginmessages->text($this->vars['yAxis']) . '/' . $this->pluginmessages->text($this->vars['xAxis']));
        // Setup titles and X-axis labels
        $graph->xaxis->SetLabelAlign('center', 'top');
        $graph->xaxis->SetTitlemargin($this->xAxisMetadata[$this->vars['xAxis']]['xAxisTitleMargin']);
        $graph->yaxis->SetTitlemargin(50);
        $graph->xaxis->title->Set($this->pluginmessages->text($this->vars['xAxis']));
        $graph->xaxis->SetTickLabels($xAxis);
        $graph->xaxis->SetLabelAngle($this->xAxisMetadata[$this->vars['xAxis']]['xAxisAngle']);
        // Setup Y-axis title
        $graph->yaxis->title->Set($this->pluginmessages->text($this->vars['yAxis']));
        if ($this->vars['plot'] == 'bar')
        {
            $this->barPlot($yAxis, $graph);
        }
        elseif ($this->vars['plot'] == 'barLine')
        {
            $this->barPlot($yAxis, $graph);
            $this->linePlot($yAxis, $graph, FALSE);
        }
        elseif ($this->vars['plot'] == 'scatter')
        {
            $this->scatterPlot($yAxis, $graph);
        }
        elseif ($this->vars['plot'] == 'scatterLine')
        {
            $this->scatterPlot($yAxis, $graph, TRUE);
        }
        elseif ($this->vars['plot'] == 'balloon')
        {
            $this->balloonPlot($yAxis, $graph);
        }
        else
        { // 'line'
            $this->linePlot($yAxis, $graph);
        }
        // Add the plot to the graph
        $this->display($graph);
    }
    /**
     * balloonCallback
     *
     * @param array $aVal
     *
     * @return array
     */
    public function balloonCallback($aVal)
    {
        // This callback will adjust the fill color and size of
        // the datapoint according to the data value according to
        //		$min = min($this->yAxis);
        $max = max($this->yAxis);
        /*		$diff = $max - $min;
                if ($aVal < ($diff / 3))
                    $c = "blue";
                elseif ($aVal < (2 * ($diff / 3)))
                    $c = "green";
                else
                    $c="red";
        */
        // Print the size between 5 and 100
        $value = floor((($aVal / 3) / $max) * 100);
        $size = floor((($aVal / 3) / $max) * 95) + 5;
        if (!$value)
        {
            ++$value;
        }

        return [$size, "", $this->colour->sequence[$value]];
        //		return array(floor($aVal / 3), "", $c);
    }
    /**
     * Make the menus
     *
     * @param array $menuArray
     */
    private function makeMenu($menuArray)
    {
        $this->menus = [$menuArray[0] => [$this->pluginmessages->text('menu') => "init"]];
    }
    /**
     * Display options for the x and y axes
     *
     * @return string
     */
    private function chooseOptions()
    {
        $yAxisTypes = $this->yAxisOptions();
        $jScript = 'index.php?action=visualize_visualizeOptions';
        $jsonArray[] = [
            'startFunction' => 'triggerFromSelect',
            'script' => "$jScript",
            'triggerField' => 'yAxis',
            'targetDiv' => 'xAxis',
        ];
        $js = AJAX\jActionForm('onchange', $jsonArray);
        $pString = HTML\tableStart('generalTable borderStyleSolid');
        $pString .= HTML\trStart();
        reset($yAxisTypes);
        $firstKey = key($yAxisTypes);
        $selected = $this->session->getVar("visualize_YAxis") ? $this->session->getVar("visualize_YAxis") : $firstKey;
        $pString .= HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("yAxis"),
            "yAxis",
            $yAxisTypes,
            $selected,
            2,
            FALSE,
            $js
        ));
        $pString .= HTML\td($this->initXAxisOptions($selected));
        $selected = $this->session->getVar("visualize_MaxXAxis") ? $this->session->getVar("visualize_MaxXAxis") : -1;
        $hint = \HTML\aBrowse('green', '', $this->coremessages->text("hint", "hint"), '#', "", $this->pluginmessages->text("maxXAxisLimit"));
        $pString .= HTML\td(FORM\textInput($this->pluginmessages->text("maxXAxis"), "maxXAxis", $selected, 3, 3) . BR . \HTML\span($hint, 'hint'));
        $pString .= HTML\td($this->choosePlot());
        $pString .= HTML\trEnd();
        $pString .= HTML\tableEnd();

        return $pString;
    }
    /**
     * visualizeOptions
     * AJAX DIV handler
     */
    public function visualizeOptions()
    {
        if ($this->vars['ajaxReturn'] == 'numResources') {
        	$pString = $this->numXAxisOptions();
        } else {
        	$pString = $this->viewsXAxisOptions();
        }
        $div = HTML\div('xAxis', $pString);
        GLOBALS::addTplVar('content', AJAX\encode_jArray(['innerHTML' => $div]));
        FACTORY_CLOSERAW::getInstance();
    }
    /**
     * Choose the type of plot
     *
     * @return string
     */
    private function choosePlot()
    {
        $plots = [
            'line' => $this->pluginmessages->text('line'),
            'bar' => $this->pluginmessages->text('bar'),
            'barLine' => $this->pluginmessages->text('barLine'),
            'scatter' => $this->pluginmessages->text('scatter'),
            'scatterLine' => $this->pluginmessages->text('scatterLine'),
            'balloon' => $this->pluginmessages->text('balloon'),
        ];
        reset($plots);
        $firstKey = key($plots);
        $selected = $this->session->getVar("visualize_Plot") ? $this->session->getVar("visualize_Plot") : $firstKey;

        return HTML\td(FORM\selectedBoxValue(
            $this->pluginmessages->text("plotType"),
            "plot",
            $plots,
            $selected,
            6
        ));
    }
    /**
     * Do a bar plot
     *
     * @param int $yAxis
     * @param object $graph
     */
    private function barPlot($yAxis, $graph)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "jpgraph", "jpgraph_bar.php"]));
        $plot = new BarPlot($yAxis);
        $graph->Add($plot);
        $plot->SetFillColor(['red','blue','green']);
        $plot->SetShadow('teal', 2, 2);
        $plot->value->SetFormat('%d');
        $plot->value->Show();
        $graph->yaxis->scale->SetGrace(5);
        $plot->SetValuePos('top');
    }
    /**
     * Do a line plot
     *
     * @param int $yAxis
     * @param object $graph
     * @param bool $showValue
     */
    private function linePlot($yAxis, $graph, $showValue = TRUE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "jpgraph", "jpgraph_line.php"]));
        $plot = new LinePlot($yAxis);
        $graph->Add($plot);
        if ($showValue)
        {
            $graph->yaxis->scale->SetGrace(5);
            $plot->value->SetFormat('%d');
            $plot->value->Show();
        }
        $plot->SetColor('red');
    }
    /**
     * Do a scatter plot
     *
     * @param int $yAxis
     * @param object $graph
     * @param bool $line
     */
    private function scatterPlot($yAxis, $graph, $line = FALSE)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "jpgraph", "jpgraph_scatter.php"]));
        $plot = new ScatterPlot($yAxis);
        if ($line)
        {
            $plot->SetLinkPoints();
        }
        $plot->mark->SetType(MARK_FILLEDCIRCLE);
        $plot->SetColor('red');
        $graph->Add($plot);
    }
    /**
     * Do a balloon plot
     *
     * @param int $yAxis
     * @param object $graph
     */
    private function balloonPlot($yAxis, $graph)
    {
        include_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "jpgraph", "jpgraph_scatter.php"]));
        $plot = new ScatterPlot($yAxis);
        // Use a lot of grace to get large scales
        $graph->yaxis->scale->SetGrace(10);
        $plot->mark->SetType(MARK_FILLEDCIRCLE);
        $plot->value->SetFormat('%d');
        $plot->value->Show();
        $plot->value->SetFont(FF_FONT1, FS_BOLD);
        $this->colour = new COLOR(100);
        $plot->mark->SetCallback([$this, "balloonCallback"]);
        $graph->Add($plot);
    }
    /**
     * Display the graph
     *
     * @param object $graph
     */
    private function display($graph)
    {
        $fileName = 'jpGraph' . \UTILS\uuid() . '.png';
        $filesDir = implode(DIRECTORY_SEPARATOR, [WIKINDX_DIR_BASE, WIKINDX_DIR_DATA_FILES, $fileName]);
        $filesUrl = implode("/", [WIKINDX_URL_BASE, WIKINDX_URL_DATA_FILES, $fileName]);
        
        $graph->Stroke($filesDir);
        $pString = HTML\img($filesUrl, $this->config->width, $this->config->height);
        $size = count($this->xAxis);
        if (array_key_exists('maxXAxis', $this->vars) && ($this->vars['maxXAxis'] > 0))
        {
            $p = $this->links($size);
            $p .= FORM\formEnd();
            $pString .= \HTML\p($p);
        }
        $pString .= HTML\p(\FORM\closePopup($this->coremessages->text("misc", "closePopup")), "right");
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
    /**
     * links
     *
     * @param int $size
     *
     * @return string
     */
    private function links($size)
    {
        $previous = $next = FALSE;
        if ($this->vars['maxXAxis'] == -1)
        {
            $this->vars['maxXAxis'] = $size;
        }
        $links = htmlentities("&yAxis=" . $this->vars['yAxis']) .
                htmlentities("&xAxis=" . $this->vars['xAxis']) .
                htmlentities("&maxXAxis=" . $this->vars['maxXAxis']) .
                htmlentities("&plot=" . $this->vars['plot']);
        $icons = FACTORY_LOADICONS::getInstance();
        if (array_key_exists('start', $this->vars) && $this->vars['start'])
        {
            $previousStart = $this->vars['start'] - $this->vars['maxXAxis'];
            $nextStart = $this->vars['maxXAxis'] + $this->vars['start'];
            $previous = \HTML\a(
                $icons->getClass("previous"),
                $icons->getHTML("previous"),
                "index.php?action=visualize_visualize" . htmlentities("&start=" . $previousStart) . $links
            );
            if (($this->vars['start'] + $this->vars['maxXAxis']) < $size)
            {
                $next = \HTML\a(
                    $icons->getClass("next"),
                    $icons->getHTML("next"),
                    "index.php?action=visualize_visualize" . htmlentities("&start=" . $nextStart) . $links
                );
            }
        }
        elseif ($this->vars['maxXAxis'] < $size)
        {
            $next = \HTML\a(
                $icons->getClass("next"),
                $icons->getHTML("next"),
                "index.php?action=visualize_visualize" . htmlentities("&start=" . $this->vars['maxXAxis']) . $links
            );
        }
        if ($previous && $next)
        {
            return "$previous&nbsp;&nbsp;$next";
        }
        elseif ($previous)
        {
            return "$previous";
        }
        else
        {
            return "$next";
        }
    }
    /**
     * getData
     */
    private function getData()
    {
        if ($this->xAxisMetadata[$this->vars['xAxis']]['countField'])
        {
            $yAxisField = $this->xAxisMetadata[$this->vars['xAxis']]['countField'];
        }
        else
        {
            $yAxisField = $this->vars['xAxis'];
        }
        if ($this->xAxisMetadata[$this->vars['xAxis']]['sql'])
        {
            $recordSet = $this->db->query($this->xAxisMetadata[$this->vars['xAxis']]['sql']);
        }
        else
        {
            $this->db->orderBy($yAxisField);
            $recordSet = $this->db->selectCount($this->xAxisMetadata[$this->vars['xAxis']]['table'], $yAxisField);
        }
        if (!$this->db->numRows($recordSet))
        {
            throw new JpGraphException($this->pluginmessages->text('noData'));
        }
        $yearsCount = [];
        while ($row = $this->db->fetchRow($recordSet))
        {
            if ($this->xAxisMetadata[$this->vars['xAxis']]['isNumeric'])
            {
                if (is_numeric($row[$yAxisField]))
                {
                    if ($this->xAxisMetadata[$this->vars['xAxis']]['messagesArray'])
                    {
                        $this->xAxis[] = $this->coremessages->text(
                            $this->xAxisMetadata[$this->vars['xAxis']]['messagesArray'],
                            $row[$yAxisField]
                        );
                    }
                    else if ($this->vars['xAxis'] == 'resourceViewsMonths') 
                    {
                    	$year = substr($row[$yAxisField], 0, 4);
                    	$month = substr($row[$yAxisField], -2);
                    	$this->xAxis[] = $month . '/' . $year;
                    }
                    else if ($this->vars['xAxis'] == 'resourceViewsYears') 
                    {
                    	$year = substr($row[$yAxisField], 0, 4);
                    	$this->xAxis[] = $year;
                    }
                    else
                    {
                        $this->xAxis[] = $row[$yAxisField];
                    }
                    $this->yAxis[] = $row['count'];
                }
            }
            else
            {
                if ($this->xAxisMetadata[$this->vars['xAxis']]['messagesArray'])
                {
                    $this->xAxis[] = $this->coremessages->text(
                        $this->xAxisMetadata[$this->vars['xAxis']]['messagesArray'],
                        $row[$yAxisField]
                    );
                }
                elseif ($this->xAxisMetadata[$this->vars['xAxis']]['labelField'])
                {
                    $this->xAxis[] = $row[$this->xAxisMetadata[$this->vars['xAxis']]['labelField']];
                }
                else
                {
                    $this->xAxis[] = $row[$yAxisField];
                }
                $this->yAxis[] = $row['count'];
            }
        }
    }
    /**
     * Get tables as per the field
     *
     * @return array
     */
    private function getXAxisMetadata()
    {
        return [
            'resourceyearYear1' => ['table' => 'resource_year',
                'isNumeric' => TRUE,
                'xAxisMargin' => 100,
                'xAxisTitleMargin' => 55,
                'xAxisAngle' => 45,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => FALSE,
                'sql' => FALSE,
                'countField' => FALSE,
                'labelField' => FALSE,
            ],
            'resourceType' => ['table' => 'resource',
                'isNumeric' => FALSE,
                'xAxisMargin' => 200,
                'xAxisTitleMargin' => 170,
                'xAxisAngle' => 90,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => 'resourceType',
                'sql' => FALSE,
                'countField' => FALSE,
                'labelField' => FALSE,
            ],
            'keywordKeyword' => ['table' => FALSE,
                'isNumeric' => FALSE,
                'xAxisMargin' => 200,
                'xAxisTitleMargin' => 170,
                'xAxisAngle' => 90,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => FALSE,
                'sql' => "SELECT `resourcekeywordKeywordId`, COUNT(`resourcekeywordKeywordId`) AS count, `keywordKeyword` FROM resource_keyword LEFT OUTER JOIN keyword ON `keywordId` = `resourcekeywordKeywordId` WHERE (`resourcekeywordResourceId` IS NOT NULL) AND (`keywordKeyword` IS NOT NULL) GROUP BY `resourcekeywordKeywordId`, `keywordKeyword` ORDER BY REPLACE( REPLACE(`keywordKeyword`, '{', ''), '}', '') ASC",
                'countField' => FALSE,
                'labelField' => FALSE,
            ],
            'categoryCategory' => ['table' => FALSE,
                'isNumeric' => FALSE,
                'xAxisMargin' => 200,
                'xAxisTitleMargin' => 160,
                'xAxisAngle' => 90,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => FALSE,
                'sql' => "SELECT `categoryId`, `categoryCategory`, `count` FROM (SELECT `resourcecategoryCategoryId`, COUNT(`resourcecategoryCategoryId`) AS count FROM resource_category WHERE (`resourcecategoryCategoryId` IS NOT NULL) GROUP BY `resourcecategoryCategoryId`) AS t LEFT OUTER JOIN category ON `categoryId` = `resourcecategoryCategoryId` ORDER BY REPLACE( REPLACE(`categoryCategory`, '{', ''), '}', '') ASC",
                'countField' => FALSE,
                'labelField' => FALSE,
            ],
            'journal' => ['table' => FALSE,
                'isNumeric' => FALSE,
                'xAxisMargin' => 300,
                'xAxisTitleMargin' => 260,
                'xAxisAngle' => 90,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => FALSE,
                'sql' => "SELECT `collectionId`, COUNT(`collectionId`) AS count , `resourcemiscCollection`, `collectionType`, `collectionTitle` FROM resource_misc LEFT OUTER JOIN collection ON `collectionId` = `resourcemiscCollection` WHERE (`collectionType` = 'journal') AND (`collectionId` IS NOT NULL) GROUP BY `collectionId`, `resourcemiscCollection`, `collectionType`, `collectionTitle` ORDER BY REPLACE( REPLACE(`collectionTitle`, '{', ''), '}', '') ASC",
                'countField' => 'collectionId',
                'labelField' => 'collectionTitle',
            ],
            'proceedings' => ['table' => FALSE,
                'isNumeric' => FALSE,
                'xAxisMargin' => 300,
                'xAxisTitleMargin' => 260,
                'xAxisAngle' => 90,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => FALSE,
                'sql' => "SELECT `collectionId`, COUNT(`collectionId`) AS count, `resourcemiscCollection`, `collectionType`, `collectionTitle` FROM resource_misc LEFT OUTER JOIN collection ON `collectionId` = `resourcemiscCollection` WHERE (`collectionType` = 'proceedings') AND (`collectionId` IS NOT NULL) GROUP BY `collectionId`, `resourcemiscCollection`, `collectionType`, `collectionTitle` ORDER BY REPLACE( REPLACE(`collectionTitle`, '{', ''), '}', '') ASC",
                'countField' => 'collectionId',
                'labelField' => 'collectionTitle',
            ],
            'resourceViewsMonths' => ['table' => FALSE,
                'isNumeric' => TRUE,
                'xAxisMargin' => 100,
                'xAxisTitleMargin' => 55,
                'xAxisAngle' => 45,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => FALSE,
                'sql' => "SELECT SUM(`statisticsresourceviewsCount`) AS `count`, `statisticsresourceviewsMonth` FROM `statistics_resource_views` GROUP BY `statisticsresourceviewsMonth` ORDER BY `statisticsresourceviewsMonth`",
                'countField' => 'statisticsresourceviewsMonth',
                'labelField' => FALSE,
            ],
            'resourceViewsYears' => ['table' => FALSE,
                'isNumeric' => TRUE,
                'xAxisMargin' => 100,
                'xAxisTitleMargin' => 55,
                'xAxisAngle' => 45,
                'xScale' => 'text',
                'yScale' => 'int',
                'messagesArray' => FALSE,
                'sql' => "SELECT SUM(`statisticsresourceviewsCount`) AS `count`, SUBSTRING(`statisticsresourceviewsMonth`, 1, 4) AS `year` FROM `statistics_resource_views` GROUP BY `year` ORDER BY `year`",
                'countField' => 'year',
                'labelField' => FALSE,
            ],
        ];
    }
    /**
     * Choose options for the Y axis
     *
     * @return array
     */
    private function yAxisOptions()
    {
        return [
            'numResources' => $this->pluginmessages->text('numResources'),
            'resourceViews' => $this->pluginmessages->text('resourceViews'),
        ];
    }
    /**
     * Choose initial options for the X axis on loading
     *
     * @param $selected
     * @return string
     */
    private function initXAxisOptions($selected)
    {
    	if ($selected == 'numResources') {
    		return $this->numXAxisOptions();
    	} else {
    		return $this->viewsXAxisOptions();
    	}
    }
    /**
     * X axis options for numResources
     *
     * @return string
     */
    private function numXAxisOptions()
    {
        $xAxisTypes = [
            'resourceType' => $this->pluginmessages->text('resourceType'),
            'resourceyearYear1' => $this->pluginmessages->text('resourceyearYear1'),
            'keywordKeyword' => $this->pluginmessages->text('keywordKeyword'),
            'categoryCategory' => $this->pluginmessages->text('categoryCategory'),
            'journal' => $this->pluginmessages->text('journal'),
            'proceedings' => $this->pluginmessages->text('proceedings'),
        ];
        reset($xAxisTypes);
        $firstKey = key($xAxisTypes);
        $selected = $this->session->getVar("visualize_XAxis") ? $this->session->getVar("visualize_XAxis") : $firstKey;
        if ($selected && !array_key_exists($selected, $xAxisTypes)) {
        	$selected = $firstKey;
        }
        return HTML\div("xAxis", FORM\selectedBoxValue(
            $this->pluginmessages->text("xAxis"),
            "xAxis",
            $xAxisTypes,
            $selected,
            6
        ));
    }
    /**
     * X axis options for resource views
     *
     * @return string
     */
    private function viewsXAxisOptions()
    {
        $xAxisTypes = [
            'resourceViewsMonths' => $this->pluginmessages->text('resourceViewsMonths'),
            'resourceViewsYears' => $this->pluginmessages->text('resourceViewsYears'),
        ];
        reset($xAxisTypes);
        $firstKey = key($xAxisTypes);
        $selected = $this->session->getVar("visualize_XAxis") ? $this->session->getVar("visualize_XAxis") : $firstKey;
        if ($selected && !array_key_exists($selected, $xAxisTypes)) {
        	$selected = $firstKey;
        }
        return HTML\div("xAxis", FORM\selectedBoxValue(
            $this->pluginmessages->text("xAxis"),
            "xAxis",
            $xAxisTypes,
            $selected,
            2
        ));
    }
    /**
     * Validate input and store in session
     *
     * @return bool
     */
    private function validate()
    {
        if (array_key_exists('yAxis', $this->vars) && $this->vars['yAxis'])
        {
            $this->session->setVar("visualize_YAxis", $this->vars['yAxis']);
        }
        else
        {
            return FALSE;
        }
        if (array_key_exists('xAxis', $this->vars) && $this->vars['xAxis'])
        {
            $this->session->setVar("visualize_XAxis", $this->vars['xAxis']);
        }
        else
        {
            return FALSE;
        }
        if (array_key_exists('plot', $this->vars) && $this->vars['plot'])
        {
            $this->session->setVar("visualize_Plot", $this->vars['plot']);
        }
        else
        {
            return FALSE;
        }
        if (array_key_exists('maxXAxis', $this->vars) && $this->vars['maxXAxis'])
        {
            if (!preg_match("#^(-[0-9]{1,}|[0-9]{1,})$#", $this->vars['maxXAxis']))
            { // need to check either '-1' or a positive integer
                return FALSE;
            }
            $this->session->setVar("visualize_MaxXAxis", $this->vars['maxXAxis']);
        }
        else
        {
            return FALSE;
        }

        return TRUE;
    }
    /**
     * bad Input function
     *
     * @param mixed $error
     */
    private function badInput($error)
    {
        $pString = HTML\p($error, 'error');
        $pString .= HTML\p(\FORM\closePopup($this->coremessages->text("misc", "closePopup")), "left");
        GLOBALS::addTplVar('content', $pString);
        FACTORY_CLOSEPOPUP::getInstance();
    }
}
/**
 * From https://stackoverflow.com/questions/1211705/paint-me-a-rainbow and
 * https://stackoverflow.com/questions/3597417/php-hsv-to-rgb-formula-comprehension#3642787
 */
class COLOR
{
    /** array */
    public $sequence = [];
    /**
     * constructor fills $sequence with a list of colours as long as the $count param
     *
     * @param mixed $count
     * @param mixed $s
     * @param mixed $l
     */
    public function __construct($count, $s = .5, $l = .5)
    {
        $index = 1;
        for ($h = 0; $h <= .85; $h += .85 / $count)
        {    //.85 is pretty much in the middle of the violet spectrum
            $this->sequence[$index++] = '#' . color::hexHSLtoRGB($h, $s, $l);
        }
    }
    /**
     * From https://stackoverflow.com/questions/1211705/paint-me-a-rainbow and
     * https://stackoverflow.com/questions/3597417/php-hsv-to-rgb-formula-comprehension#3642787
     *
     * @param mixed $h
     * @param mixed $s
     * @param mixed $l
     *
     * @return array
     */
    public static function HSLtoRGB($h, $s, $l)
    {
        $r = $l;
        $g = $l;
        $b = $l;
        $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : (l + $s - l * $s);
        if ($v > 0)
        {
            $m;
            $sv;
            $sextant;
            $fract;
            $vsf;
            $mid1;
            $mid2;
            $m = $l + $l - $v;
            $sv = ($v - $m) / $v;
            $h *= 6.0;
            $sextant = floor($h);
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;
            switch ($sextant) {
                    case 0:
                          $r = $v;
                          $g = $mid1;
                          $b = $m;

                          break;
                    case 1:
                          $r = $mid2;
                          $g = $v;
                          $b = $m;

                          break;
                    case 2:
                          $r = $m;
                          $g = $v;
                          $b = $mid1;

                          break;
                    case 3:
                          $r = $m;
                          $g = $mid2;
                          $b = $v;

                          break;
                    case 4:
                          $r = $mid1;
                          $g = $m;
                          $b = $v;

                          break;
                    case 5:
                          $r = $v;
                          $g = $m;
                          $b = $mid2;

                          break;
              }
        }

        return ['r' => floor($r * 255.0),
            'g' => floor($g * 255.0),
            'b' => floor($b * 255.0),
        ];
    }
    /**
     * return a hex code from hsv values
     *
     * @param mixed $h
     * @param mixed $s
     * @param mixed $l
     *
     * @return mixed
     */
    public static function hexHSLtoRGB($h, $s, $l)
    {
        $rgb = self::HSLtoRGB($h, $s, $l);
        $hex = base_convert($rgb['r'], 10, 16) . base_convert($rgb['g'], 10, 16) . base_convert($rgb['b'], 10, 16);

        return $hex;
    }
}
