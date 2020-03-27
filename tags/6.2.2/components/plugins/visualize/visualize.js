/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
**********************************************************************************/

/**
* visualize.js
*/

function visualizePopUp()
{
    var yAxisValue = document.querySelector('select[name=yAxis]').value;
    var xAxisValue = document.querySelector('select[name=xAxis]').value;
    var maxXAxis = document.getElementById('maxXAxis');
    var plotValue = document.querySelector('select[name=plot]').value;
    var maxXAxisValue = maxXAxis.value;
	var objectReturn = new coreBrowserDimensions();
	var w = Math.round(objectReturn.browserWidth * 0.9);
	var h = Math.round(objectReturn.browserHeight * 0.9);
	var url = 'index.php?action=visualize_visualize&yAxis=' + yAxisValue + 
		'&xAxis=' + xAxisValue + 
			'&maxXAxis=' + maxXAxisValue + 
			'&plot=' + plotValue;
    var popupWindow = window.open(url, 'User', 'height=' + h + ',width=' + w + ',left=10,top=10,status,scrollbars,resizable,dependent');
    popupWindow.focus();
}
