import { displayError, displaySuccess } from "./wikindxMessages";


var xml;
var errorJSON = "ERROR: Unspecified error. This could be any number of things from not being able to connect to the WIKINDX to no resources found matching your search.";
var errorAccess = 'The WIKINDX admin has not enabled read-only access.';
var successHeartbeat = "Yes, I am alive and kicking. Try searching me . . .";
export var errorXMLHTTP = "ERROR: XMLHTTP error – could not connect to the WIKINDX.  <br/><br/>There could be any number of reasons for this including an incorrect WIKINDX URL, an incompatibility between this add-in and the WIKINDX, the WIKINDX admin has not enabled read-only access, a network error . . .";
export var xmlResponse = null;
export var searchURL;
export var selectedURL;
export var selectedName;

export function setSearchURL(text) {
  searchURL = text;
  return true;
}


export function getReference(id) {
  var wikindxURL = document.getElementById("wikindx-url");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getReference' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&id=' + encodeURI(id);
  doXml();
}

export function getCitation(id) {
  var wikindxURL = document.getElementById("wikindx-url");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  var html = '&withHtml=1';

  if (document.getElementById("wikindx-citation-html").checked) {
    html = '&withHtml=0';
  }
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getCitation' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&id=' + encodeURI(id)
    + html;
  doXml();
}

export function finalizeGetReferences(wikindxURL, ids) {
  var searchParams = document.getElementById("wikindx-finalize-order");
  var styleSelectBox = document.getElementById("wikindx-finalize-styleSelectBox");
  searchURL = wikindxURL
    + "office.php" + '?method=getBib' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&searchParams=' + encodeURI(searchParams.value)
    + '&ids=' + encodeURI(ids);
  doXml();
}

export function finalizeGetCitations(wikindxURL, ids) {
  var styleSelectBox = document.getElementById("wikindx-finalize-styleSelectBox");
  searchURL = wikindxURL 
    + "office.php" + '?method=getCiteCCs' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&ids=' + encodeURI(ids);
  doXml();
}

export function getStyles() {
  var wikindxURL = document.getElementById("wikindx-url");
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getStyles';
  doXml();
  // As this is triggered from a change in the WIKINDX selection, store also the currently selected URL and name
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  selectedURL = wikindxURL.value;
  for (var i = 0; i < jsonArray.length; i++) {
    if (jsonArray[i][0] == selectedURL) {
      selectedName = jsonArray[i][1];
      break;
    }
  }
  document.getElementById("wikindx-url-visit").href = selectedURL;
}

export function getUrlSelectBox(jsonArray) {
  var len = jsonArray.length;
  var i;
  var text = '';
  for (i = 0; i < len; i++) {
    text += '<option value="' + jsonArray[i][0] + '">' + jsonArray[i][1] + '</option>';
    if (!i) {
      selectedURL = jsonArray[i][0];
      selectedName = jsonArray[i][1];
    }
  }
  document.getElementById("wikindx-url").innerHTML = text;
  document.getElementById("wikindx-url-visit").href = selectedURL;
}

export function getSearchInputReferences(searchText) {
  var searchParams = document.getElementById("wikindx-reference-params");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  var wikindxURL = document.getElementById("wikindx-url");

  searchURL = wikindxURL.value 
    + "office.php" + '?method=getReferences' 
    + '&searchWord=' + encodeURI(searchText)
    + '&style=' + encodeURI(styleSelectBox.value)
    + '&searchParams=' + encodeURI(searchParams.value)
  doXml();
}

export function getSearchInputCitations(searchText) {
  var searchParams = document.getElementById("wikindx-citation-params");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  var wikindxURL = document.getElementById("wikindx-url");
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getCitations' 
    + '&searchWord=' + encodeURI(searchText)
    + '&style=' + encodeURI(styleSelectBox.value)
    + '&searchParams=' + encodeURI(searchParams.value);
  doXml();
}

export function userCheckHeartbeat() {
  if (heartbeat(document.getElementById("wikindx-url").value) !== true) {
    displayError(errorXMLHTTP);
    return false;
  }
  displaySuccess(successHeartbeat);
  return true;
}

export function heartbeat(url) {
  if (!url) {
    url = document.getElementById("wikindx-url").value;
  }
  searchURL = url + "office.php" + '?method=heartbeat';
  doXml();
  if (xmlResponse == null) {
    return displayError(errorXMLHTTP);
  } else if(xmlResponse == 'access denied'){
    return displayError(errorAccess); 
  }
  return true;
}

export function doXml() {
// For debugging – log message can be copied into a web browser . . .
//  console.log('doXml(): ' + searchURL);
  xmlResponse = null;
  xml.open("POST", searchURL, false);
  xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xml.onerror = function() {displayError(errorXMLHTTP); return false;};
  xml.send();
}
export function prepareXml() {
  xml = new makeHttpObject();
  xml.onreadystatechange = function () {
    if (xml.readyState == 4 && xml.status == 200) {
      try {
        xmlResponse = JSON.parse(xml.responseText);
      }
      catch (e) {
        displayError(errorJSON);
        return false;
      }
    }
  };
  return true;
}
function makeHttpObject() {
  try { return new XMLHttpRequest(); }
  catch (error) { }
  try { return new ActiveXObject("Msxml2.XMLHTTP"); }
  catch (error) { }
  try { return new ActiveXObject("Microsoft.XMLHTTP"); }
  catch (error) { }
  throw new Error("Could not create HTTP request object.");
}