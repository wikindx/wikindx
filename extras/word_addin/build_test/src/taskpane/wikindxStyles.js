import { displayError } from "./wikindxMessages";
import * as Visible from "./wikindxVisible";
import * as Xml from "./wikindxXml";

export var wikindices = [];
var errorStyles = "ERROR: Either no styles are defined on the selected WIKINDX or there are no available in-text citation styles.";
var errorStylesFinalize = "ERROR: No available in-text citation styles found in any of the wikindices used in the document.";

export function styleSelectBox() {
  var hrReturn = Xml.heartbeat(false);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }
  var styles = document.getElementById("wikindx-styles");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  Xml.getStyles();
  if (Xml.xmlResponse == null) {
    displayError(Xml.errorXMLHTTP);
    styles.style.display = "none";
    return false;
  }
  var len = Xml.xmlResponse.length;
  if (!len) {
    displayError(errorStyles);
    styles.style.display = "none";
    return false;
  }

  var styleLong, styleShort, i;
  var text = '';

  for (i = 0; i < len; i++) {
    styleShort = Xml.xmlResponse[i].styleShort;
    styleLong = Xml.xmlResponse[i].styleLong;
    text += '<option value="' + styleShort + '">' + styleLong + '</option>';
  }
  styleSelectBox.innerHTML = text;
  Visible.reset();
  styles.style.display = "block";
  return true;
}

export function finalizeGetStyles() {
  var finalArray = [];
  var tempLongNames = new Object();
  var styleLong, styleShort, i, j, len, url;
  var text = '';
  var stylesFound = false;
  for (i = 0; i < wikindices.length; i++) {
    var tempArray = [];
    url = wikindices[i][0];
    Xml.setSearchURL(url + "office.php" + '?method=getStyles');
    Xml.doXml();
    if (Xml.xmlResponse == null) {
      displayError(Xml.errorXMLHTTP);
      return false;
    }
    len = Xml.xmlResponse.length;
    if (!len) {
      continue;
    }
    stylesFound = true;
    // first run through – gather all styles from first WIKINDX
    if (!i) {
      for (j = 0; j < len; j++) {
        finalArray.push(Xml.xmlResponse[j].styleShort);
        tempLongNames[Xml.xmlResponse[j].styleShort] = Xml.xmlResponse[j].styleLong;
      }
      continue;
    } // else . . .
    for (j = 0; j < len; j++) {
      tempArray.push(Xml.xmlResponse[j].styleShort);
      tempLongNames[Xml.xmlResponse[j].styleShort] = Xml.xmlResponse[j].styleLong;
    }
    finalArray = finalArray.filter(value => tempArray.includes(value));
  }
  if (!stylesFound) {
    displayError(errorStylesFinalize);
    return false;
  }
  if (!finalArray.length) {
  // Get styles from wikindx with most intext references
    wikindices.sort(function (a, b) { return b[1] - a[1]; });
    url = wikindices[0][0];
    Xml.setSearchURL(url + "office.php" + '?method=getStyles');
    Xml.doXml();
    finalArray = [];
    tempLongNames = [];
    len = Xml.xmlResponse.length;
    for (j = 0; j < len; j++) {
      finalArray.push(Xml.xmlResponse[j].styleShort);
      tempLongNames[Xml.xmlResponse[j].styleShort] = Xml.xmlResponse[j].styleLong;
    }
  }
  for (i = 0; i < finalArray.length; i++) {
    styleShort = finalArray[i];
    styleLong = tempLongNames[finalArray[i]];
    text += '<option value="' + styleShort + '">' + styleLong + '</option>';
  }
  document.getElementById("wikindx-finalize-styleSelectBox").innerHTML = text;
  return true;
}

export function initWikindices() {
  wikindices = [];
}
export function wikindicesPush(url) {
  var len = wikindices.length;
  for (var i = 0; i < len; i++) {
    if (url == wikindices[i][0]) {
        wikindices[i][1]++;
        return;
    }
  }
  wikindices.push([url, 1]);
}