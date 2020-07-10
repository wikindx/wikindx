<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://www.isc.org/licenses/ ISC License
 */
class visualizeMessages
{
    /** array */
    public $text = [];
    
    public function __construct()
    {
        $domain = mb_strtolower(basename(__DIR__));
        
        $this->text = [
            "menu" => dgettext($domain, "Visualize"),
            "heading" => dgettext($domain, "Visualize"),
            "submit" => dgettext($domain, "Visualize"),
            "noData" => dgettext($domain, "No data in the database of selected type."),
            "yAxis" => dgettext($domain, "Y axis"),
            "xAxis" => dgettext($domain, "X axis"),
            "maxXAxis" => dgettext($domain, "Maximum no. items on the X axis"),
            "maxXAxisLimit" => dgettext($domain, "-1 is unlimited"),
            "numResources" => dgettext($domain, "No. resources"),
            "resourceType" => dgettext($domain, "Resource type"),
            "resourceyearYear1" => dgettext($domain, "Publication year"),
            "keywordKeyword" => dgettext($domain, "Keyword"),
            "categoryCategory" => dgettext($domain, "Category"),
            "journal" => dgettext($domain, "Journal"),
            "proceedings" => dgettext($domain, "Proceedings"),
            "inputMissing" => dgettext($domain, "Missing input"),
            "plotType" => dgettext($domain, "Type of plot"),
            "line" => dgettext($domain, "Line plot"),
            "bar" => dgettext($domain, "Bar plot"),
            "barLine" => dgettext($domain, "Combined bar/line plot"),
            "scatter" => dgettext($domain, "Scatter plot"),
            "scatterLine" => dgettext($domain, "Scatter plot with line"),
            "balloon" => dgettext($domain, "Balloon plot"),
            "visualize" => dgettext($domain, "Visualize"),
        ];
    }
}
