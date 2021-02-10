/*
 * Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 * See LICENSE in the project root for license information.
 */

// images references in the manifest
import "../../assets/wikindx-16.png";
import "../../assets/wikindx-32.png";
import "../../assets/wikindx-80.png";

/* global vars */
var bibEntry = '';
var inTextReference = '';
var citation = '';
var id = '';
var initialId = false;
var errorAccess = 'The WIKINDX admin has not enabled read-only access.';
var errorXMLHTTP = "ERROR: XMLHTTP error – could not connect to the WIKINDX.  <br/><br/>There could be any number of reasons for this including an incorrect WIKINDX URL, an incompatibility between this add-in and the WIKINDX, the WIKINDX admin has not enabled read-only access, a network error . . .";
var errorStyles = "ERROR: Either no styles are defined on the selected WIKINDX or there are no available in-text citation styles.";
var errorSearch = "ERROR: Missing search input.";
var errorNewUrl = "ERROR: Missing URL or name input.";
var errorDuplicateUrl = "ERROR: Duplicate URL input.";
var errorDuplicateName = "ERROR: Duplicate name input.";
var errorNoResultsReferences = "No references found matching your search.";
var errorNoResultsCitations = "No citations found matching your search.";
// var errorXMLTimeout = "ERROR: The search timed out. Consider reformulating your search."
var errorJSON = "ERROR: Unspecified error. This could be any number of things from not being able to connect to the WIKINDX to no resources found matching your search.";
var errorMissingID = "ERROR: Resource not found in the selected WIKINDX.";
var errorNoInserts = "You have not inserted any references or citations yet so there is nothing to finalize.";
var successNewUrl = "Stored new WIKINDX: ";
var successEditUrl = "Edited WIKINDX URL.";
var successRemoveUrl = "Deleted WIKINDX URL(s).";
var successRemoveAllUrls = "Deleted all WIKINDX URLs.";
var successHeartbeat = "Yes, I am alive and kicking. Try searching me . . .";
var xml;
var xmlResponse = null;
var docSelection;
var searchURL;
var visibleElements = [];
var selectedURL;
var selectedName;
var storedIDs = new Object();
var ooxmlVanishStart = "<w:p xmlns:w='http://schemas.microsoft.com/office/word/2003/wordml'><w:r><w:rPr><w:vanish/></w:rPr><w:t>";
var ooxmlVanishEnd = "</w:t></w:r></w:p>";


/* global document, Office, Word */

Office.onReady(info => {
  if (info.host === Office.HostType.Word) {
  // Determine if the user's version of Office supports all the Office.js APIs that are used in the tutorial.
    if (!Office.context.requirements.isSetSupported('WordApi', '1.3')) {
        console.log('Sorry. The tutorial add-in uses Word.js APIs that are not available in your version of Office.');
    }
  
// Prepare for XMLHTTP connections
    if (prepareXml() == false) {
      displayError(errorXMLHTTP);
      return false;
    };


/** 
 * Comment out this line in production. Uncommented, it removes all persistent settings in the open document . . .
*/
// Office.context.document.settings.remove('wikindx-id');

// Assign event handlers and other initialization logic.
    document.getElementById("wikindx-action").onchange = displayInit;
    document.getElementById("wikindx-search").onclick = wikindxSearch;
    document.getElementById("wikindx-url").onchange = styleSelectBox;
    document.getElementById("wikindx-styleSelectBox").onchange = reset;
    document.getElementById("wikindx-reference-params").onchange = reset;
    document.getElementById("wikindx-citation-params").onchange = reset;
    document.getElementById("wikindx-refSelectBox").onchange = displayReference;
    document.getElementById("wikindx-citeSelectBox").onchange = displayCitation;
    document.getElementById("wikindx-insert-reference").onclick = insertReference;
    document.getElementById("wikindx-insert-citation").onclick = insertCitation;
    document.getElementById("wikindx-url-store").onclick = urlRegister;
    document.getElementById("wikindx-url-add").onclick = urlAddDisplay;
    document.getElementById("wikindx-url-edit1").onclick = urlEditDisplay;
    document.getElementById("wikindx-url-edit2").onclick = urlEdit;
    document.getElementById("wikindx-close-url-entry").onclick = wikindxClose;
    document.getElementById("wikindx-close-url-edit").onclick = wikindxClose;
    document.getElementById("wikindx-url-delete").onclick = urlDeleteDisplay;
    document.getElementById("wikindx-display-about").onclick = wikindxDisplayAbout;
    document.getElementById("wikindx-url-heartbeat").onclick = userCheckHeartbeat;

//
// For debugging only, uncomment. Otherwise, leave commented out. Uncommented, it will remove all stored URLs from the Office environment. . .
 //     window.localStorage.removeItem('wikindx-localStorage');
//

// Check we have localStorage set up
    checkLocalStorage(false);
  }
  readIDs();
});

function displayInit() {
  document.getElementById("wikindx-about").style.display = "none";
  document.getElementById("wikindx-display-about").src = "../../assets/lightbulb_off.png";
  if (document.getElementById("wikindx-action").value == 'references') {
    hideVisible(["wikindx-messages", "wikindx-display-results", "wikindx-citation-order"]);
    document.getElementById("wikindx-url-management").style.display = "none";
    document.getElementById("wikindx-action-title-citations").style.display = "none";
    document.getElementById("wikindx-action-title-finalize").style.display = "none";
    document.getElementById("wikindx-finalize").style.display = "none";
    document.getElementById("wikindx-action-title-references").style.display = "block";
    document.getElementById("wikindx-reference-order").style.display = "block";
    document.getElementById("wikindx-search-parameters").style.display = "block";
  } else if (document.getElementById("wikindx-action").value == 'citations') {
    hideVisible(["wikindx-messages", "wikindx-display-results", "wikindx-reference-order"]);
    document.getElementById("wikindx-url-management").style.display = "none";
    document.getElementById("wikindx-action-title-references").style.display = "none";
    document.getElementById("wikindx-action-title-finalize").style.display = "none";
    document.getElementById("wikindx-finalize").style.display = "none";
    document.getElementById("wikindx-action-title-citations").style.display = "block";
    document.getElementById("wikindx-citation-order").style.display = "block";
    document.getElementById("wikindx-search-parameters").style.display = "block";
  } else if (document.getElementById("wikindx-action").value == 'finalize') {
    hideVisible(["wikindx-messages", "wikindx-display-results", "wikindx-url-management", "wikindx-search-parameters"]);
    document.getElementById("wikindx-action-title-references").style.display = "none";
    document.getElementById("wikindx-action-title-citations").style.display = "none";
    document.getElementById("wikindx-action-title-finalize").style.display = "block";
    document.getElementById("wikindx-finalize").style.display = "block";
    finalizeDisplay();
  }
}

function finalizeDisplay() {
  if (Object.keys(storedIDs).length === 0) { // Nothing stored yet for this document
      displayError(errorNoInserts);
      return false;
  }
  console.table(storedIDs);
  finalize();
}

function finalize()
{ 
  Word.run(function (context) {
    docSelection = context.document.getSelection();
    var text = '<w:rPr><w:rFonts w:eastAsia="Times New Roman" w:cs="Times New Roman (Body CS)"/><w:vanish/></w:rPr><w:t>NOW HIDDEN</w:t>';
    text = "<w:p xmlns:w='http://schemas.microsoft.com/office/word/2003/wordml'><w:r><w:rPr><w:vanish/></w:rPr><w:t>blah</w:t></w:r></w:p>";
 //   text = "<w:p xmlns:w='http://schemas.microsoft.com/office/word/2003/wordml'><w:r><w:rPr><w:b/><w:b-cs/><w:color w:val='FF0000'/><w:sz w:val='28'/><w:sz-cs w:val='28'/></w:rPr><w:t>Hello world (this should be bold, red, size 14).</w:t></w:r></w:p>";

    docSelection.insertOoxml(wrapCitation('A Citation!'), "Replace");  
      return context.sync().then(function () {
        console.log('Text insertion complete');
      });
    })
  .catch(function (error) {
      console.log("Error: " + error);
      if (error instanceof OfficeExtension.Error) {
          console.log("Debug info: " + JSON.stringify(error.debugInfo));
          displayError(JSON.stringify(error));
          return false;
      }
  });
}

function wrapCitation(text) {
  return ooxmlVanishStart + text + ooxmlVanishEnd + ' ' + ooxmlVanishStart + text + ooxmlVanishEnd;
}

function displayReference() {
  xmlResponse = null;
  getReference();
  if (xmlResponse == 'Bad ID') {
    displayError(errorMissingID);
  }
  bibEntry = xmlResponse.bibEntry;
  document.getElementById("wikindx-display-ref").innerHTML = '</br>' + bibEntry;
  return true;
}
function displayCitation() {
  xmlResponse = null;
  getCitation();
  if (xmlResponse == 'Bad ID') {
    displayError(errorMissingID);
  }
  citation = xmlResponse.citation;
  bibEntry = xmlResponse.bibEntry;
  document.getElementById("wikindx-display-cite").innerHTML = '</br>' + citation + '<br/><br/>' + bibEntry;
  return true;
}

function wikindxDisplayAbout() {
// Show About
  if (document.getElementById("wikindx-about").style.display == "none") {
    if (localStorage()) {
      document.getElementById("wikindx-about-begin").style.display = "none";
    } else {
      document.getElementById("wikindx-about-begin").style.display = "block";
    }
    document.getElementById("wikindx-about").style.display = "block";
    hideVisible(["wikindx-url-management", "wikindx-messages", "wikindx-display-results", "wikindx-search-parameters", 
      "wikindx-finalize", "wikindx-action-title-references", "wikindx-action-title-citations", "wikindx-action-title-finalize"]);
    document.getElementById("wikindx-display-about").src = "../../assets/lightbulb.png";
  } else {
// Hide About and turn back on what was previously visible
    document.getElementById("wikindx-about").style.display = "none";
    document.getElementById("wikindx-display-about").src = "../../assets/lightbulb_off.png";
    retrieveVisible();
    checkLocalStorage(true);
  }
}

function hideVisible(idArray) {
  // store what is currently visible then hide
  visibleElements = []; // Empty out storage . . .
  for (var i = 0; i < idArray.length; i++) {
    if (document.getElementById(idArray[i]).style.display != "none") {
      visibleElements.push(idArray[i]);
      document.getElementById(idArray[i]).style.display = "none";
    }
  }
}

function retrieveVisible() {
  // Retrieve what was visible and show again
  for (var i = 0; i < visibleElements.length; i++) {
    document.getElementById(visibleElements[i]).style.display = "block";
  }
}

function checkLocalStorage(displayUrlEntry) {
  if (!localStorage()) {
    document.getElementById("wikindx-search-parameters").style.display = "none";
    document.getElementById("wikindx-close-url-entry").style.display = "none";
    if (displayUrlEntry){
      document.getElementById("wikindx-url-management").style.display = "block";
      document.getElementById("wikindx-url-entry").style.display = "block";
      document.getElementById("wikindx-about").style.display = "none";
    } else {
      document.getElementById("wikindx-url-management").style.display = "none";
      document.getElementById("wikindx-about").style.display = "block";
      document.getElementById("wikindx-about-begin").style.display = "block";
      document.getElementById("wikindx-display-about").src = "../../assets/lightbulb.png";
    }
    return false;
  } else {
    if (!visibleElements.length) {
      document.getElementById("wikindx-about-begin").style.display = "none";
      document.getElementById("wikindx-url-management").style.display = "none";
      document.getElementById("wikindx-search-parameters").style.display = "block";
      styleSelectBox(); // Preload with first value from wikindx-url select box
    } else {
      retrieveVisible();
    }
  }
  return true;
}

function wikindxClose() {
  retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  document.getElementById("wikindx-search-parameters").style.display = "block";
}

function urlEditDisplay() {
  hideVisible(["wikindx-search-parameters", "wikindx-display-results"]);
  document.getElementById("wikindx-urls-remove").style.display = "none";
  document.getElementById("wikindx-url-entry").style.display = "none";
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-edit-url").value = selectedURL;
  document.getElementById("wikindx-edit-name").value = selectedName;
  document.getElementById("wikindx-url-edit").style.display = "block";
  document.getElementById("wikindx-url-management").style.display = "block";
}
function urlEdit() {
  var editUrl = document.getElementById("wikindx-edit-url");
  var url = editUrl.value.trim();
  var editName = document.getElementById("wikindx-edit-name");
  var name = editName.value.trim();
  if (!url) {
    displayError(errorNewUrl);
    return;
  }
  if (!name) {
    displayError(errorNewUrl);
    return;
  }
  // Add trailing '/'
  if (url.slice(-1) != '/'){
    url += '/';
  }
  if (url == selectedURL && name == selectedName) { // No change
    visibleElements.push("wikindx-search-parameters");
    retrieveVisible();
    document.getElementById("wikindx-url-management").style.display = "none";
    displaySuccess(successEditUrl);
    return;
  }
  var hrReturn = heartbeat(url);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }

  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  // get index of original
  for (var i = 0; i < jsonArray.length; i++) {
      if (selectedURL == jsonArray[i][0]) {
        var selected_i = i;
        break;
      }
  }
  for (var i = 0; i < jsonArray.length; i++) {
    if (i == selected_i) {
      continue;
    }
    if ((url == jsonArray[i][0])) {
      displayError(errorDuplicateUrl);
      return;
    } else if (name == jsonArray[i][1]) {
      displayError(errorDuplicateName);
      return;
    }
  }
  // If we get here, we're cleared to stored the edits
  jsonArray[selected_i][0] = url;
  jsonArray[selected_i][1] = name;

  window.localStorage.setItem('wikindx-localStorage', JSON.stringify(jsonArray));
  getUrlSelectBox(jsonArray);
  visibleElements.push("wikindx-search-parameters");
  retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  displaySuccess(successEditUrl);
  return;
}

function urlAddDisplay() {
  hideVisible(["wikindx-search-parameters", "wikindx-display-results"]);
  document.getElementById("wikindx-urls-remove").style.display = "none";
  document.getElementById("wikindx-url-edit").style.display = "none";
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-url-entry").style.display = "block";
  document.getElementById("wikindx-url-management").style.display = "block";
}
function urlRegister() {
  var newUrl = document.getElementById("wikindx-new-url");
  var url = newUrl.value.trim();
  var newName = document.getElementById("wikindx-new-url-name");
  var name = newName.value.trim();
  if (!url) {
    displayError(errorNewUrl);
    return;
  }
  if (!name) {
    displayError(errorNewUrl);
    return;
  }
  // Add trailing '/'
  if (url.slice(-1) != '/'){
    url += '/';
  }
  var hrReturn = heartbeat(url);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }
  if (window.localStorage.getItem('wikindx-localStorage') == null) {
    var jsonArray = [[url, name]];
  } else {
    var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
    var len = jsonArray.length;
    var i;
    for (i = 0; i < len; i++) {
      if ((url == jsonArray[i][0])) {
        displayError(errorDuplicateUrl);
        return;
      } else if (name == jsonArray[i][1]) {
        displayError(errorDuplicateName);
        return;
      }
    }
    jsonArray.push([url, name]);
  }
  window.localStorage.setItem('wikindx-localStorage', JSON.stringify(jsonArray));
  getUrlSelectBox(jsonArray);
  visibleElements.push("wikindx-search-parameters");
  retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  styleSelectBox(); // Preload with first value from wikindx-url select box
  displaySuccess(successNewUrl + name + ' (' + url + ')');
  return;
}

function urlDeleteDisplay() {
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var len = jsonArray.length;
  var i, id;
  var text = '';
  for (i = 0; i < len; i++) {
    id = jsonArray[i][0] + '--WIKINDX--' + jsonArray[i][1];
    text += '<input type="checkbox" id="' + id + '" name="' + id + '">' 
      + '<label for="' + id + '"> ' + jsonArray[i][1] + ': ' + jsonArray[i][0] + '</label><br/>';
  }
  text += '<button class="button" id="wikindx-url-remove" alt="Delete URLs" title="Delete URLs">Delete URLs</button>';
  text += '<button class="button" id="wikindx-close-url-remove" alt="Close" title="Close">Close</button>';
  document.getElementById("wikindx-urls-remove").innerHTML = text;
  hideVisible(["wikindx-search-parameters", "wikindx-display-results"]);
  document.getElementById("wikindx-url-entry").style.display = "none";
  document.getElementById("wikindx-url-edit").style.display = "none";
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-urls-remove").style.display = "block";
  document.getElementById("wikindx-url-management").style.display = "block";
  document.getElementById("wikindx-url-remove").onclick = urlRemove;
  document.getElementById("wikindx-close-url-remove").onclick = wikindxClose;
}

function urlRemove() {
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var len = jsonArray.length;
  var i, id;
  var removeCount = 0;
  var split = [];
  var keep = [];
  var newArray = [];
  // First compare
  for (i = 0; i < len; i++) {
      id = jsonArray[i][0] + '--WIKINDX--' + jsonArray[i][1];
      if (document.getElementById(id).checked == false){
        split = id.split('--WIKINDX--');
        keep.push([split[0], split[1]]);
      } else {
          ++removeCount;
      }
  }
  if (!removeCount) {
    urlDeleteDisplay();
    return;
  }

  if (removeCount == len) { // Have we completely emptied the list?
    window.localStorage.removeItem('wikindx-localStorage');
    document.getElementById("wikindx-about-begin").style.display = "block";
    document.getElementById("wikindx-urls-remove").style.display = "none";
    urlAddDisplay();
    document.getElementById("wikindx-close-url-entry").style.display = "none";
    displaySuccess(successRemoveAllUrls);
    return;
  }

  window.localStorage.setItem('wikindx-localStorage', JSON.stringify(keep));
  getUrlSelectBox(keep);
  styleSelectBox(); // Preload with first value from wikindx-url select box
  document.getElementById("wikindx-url-management").style.display = "none";
  retrieveVisible();
  displaySuccess(successRemoveUrl);
}

function localStorage() {
    if (window.localStorage.getItem('wikindx-localStorage') == null) {console.log('HERE');
        return false;
    }
    var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
    getUrlSelectBox(jsonArray);
    return true;
}

function getUrlSelectBox(jsonArray) {
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

function styleSelectBox() {
  var hrReturn = heartbeat(false);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }
 xmlResponse = null;
  var styles = document.getElementById("wikindx-styles");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  getStyles();
  if (xmlResponse == null) {
    displayError(errorXMLHTTP);
    styles.style.display = "none";
    return false;
  }
  var len = xmlResponse.length;
  if (!len) {
    displayError(errorStyles);
    styles.style.display = "none";
    return false;
  }

  var styleLong, styleShort, i;
  var text = '';

  for (i = 0; i < len; i++) {
    styleShort = xmlResponse[i].styleShort;
    styleLong = xmlResponse[i].styleLong;
    text += '<option value="' + styleShort + '">' + styleLong + '</option>';
  }
  styleSelectBox.innerHTML = text;
  styles.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-display-results").style.display = "none";
  return true;
}

function reset() {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-messages").style.display = "none";
}

function wikindxSearch() {
  var hrReturn = heartbeat(false);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  } 
  // Check a style is available
//  if (document.getElementById("wikindx-search-results").style.display == "none") {
//    return styleSelectBox();
//  }
  var searchText = document.getElementById("wikindx-search-text").value;
  searchText = searchText.trim();
  searchText = searchText.replace(/[\u201C\u201D]/g, '"'); // Really!!!!! Ensure smart quotes are standard double quotes!!!!!
  if (!searchText) {
    displayError(errorSearch);
    return false;
  }
  if (document.getElementById("wikindx-action").value == 'references') {
    return searchReferences(searchText);
  } else if (document.getElementById("wikindx-action").value == 'citations') {
    return searchCitations(searchText);
  }
}

function searchCitations(searchText) {
  xmlResponse = null;
  getSearchInputCitations(searchText);
  if (xmlResponse == null) {
    displayError(errorNoResultsCitations);
    return false;
  }
  printSearchResultsCitations();
  return true;
}

function searchReferences(searchText) {
  xmlResponse = null;
  getSearchInputReferences(searchText);
  if (xmlResponse == null) {
    displayError(errorNoResultsReferences);
    return false;
  }
  printSearchResultsReferences();
  return true;
}

function printSearchResultsReferences() {
  var refSelectBox = document.getElementById("wikindx-refSelectBox");
  var len = xmlResponse.length;
  var i;
  var text = '';

  for (i = 0; i < len; i++) {
    bibEntry = xmlResponse[i].bibEntry;
    id = xmlResponse[i].id;
    text += '<option value="' + id + '">' + bibEntry + '</option>';
    if (i == 0) {
      initialId = id;
    }
  }
  displayReference();
  refSelectBox.innerHTML = text;
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-display-cite").style.display = "none";
  document.getElementById("wikindx-insert-citeSection").style.display = "none";
  document.getElementById("wikindx-citeSelectBox").style.display = "none";
  document.getElementById("wikindx-display-ref").style.display = "block";
  document.getElementById("wikindx-insert-refSection").style.display = "block";
  document.getElementById("wikindx-display-results").style.display = "block";
  document.getElementById("wikindx-refSelectBox").style.display = "block";
}

function printSearchResultsCitations() {
  var citeSelectBox = document.getElementById("wikindx-citeSelectBox");
  var len = xmlResponse.length;
  var i;
  var text = '';

  for (i = 0; i < len; i++) {
    citation = xmlResponse[i].citation;
    id = xmlResponse[i].id;
    text += '<option value="' + id + '">' + citation + '</option>';
    if (i == 0) {
      initialId = id;
    }
  }
  displayCitation();
  citeSelectBox.innerHTML = text;
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-display-ref").style.display = "none";
  document.getElementById("wikindx-insert-refSection").style.display = "none";
  document.getElementById("wikindx-refSelectBox").style.display = "none";
  document.getElementById("wikindx-display-cite").style.display = "block";
  document.getElementById("wikindx-insert-citeSection").style.display = "block";
  document.getElementById("wikindx-display-results").style.display = "block";
  document.getElementById("wikindx-citeSelectBox").style.display = "block";
}

function getReference() {
  var wikindxURL = document.getElementById("wikindx-url");
  if (!initialId) {
    var id = document.getElementById("wikindx-refSelectBox").value;
  } else {
    var id = initialId;
    initialId = false;
  }
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  var html = '&withHtml=1';

  if (document.getElementById("wikindx-reference-html").checked) {
    html = '&withHtml=0';
  }
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getReference' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&id=' + encodeURI(id)
    + html;
  doXml();
}

function getCitation() {
  var wikindxURL = document.getElementById("wikindx-url");
  if (!initialId) {
    var id = document.getElementById("wikindx-citeSelectBox").value;
  } else {
    var id = initialId;
    initialId = false;
  }
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

function getStyles() {
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

function getSearchInputReferences(searchText) {
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

function getSearchInputCitations(searchText) {
  var searchParams = document.getElementById("wikindx-citation-params");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  var wikindxURL = document.getElementById("wikindx-url");
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getCitations' 
    + '&searchWord=' + encodeURI(searchText)
    + '&style=' + encodeURI(styleSelectBox.value)
    + '&searchParams=' + encodeURI(searchParams.value);
  console.log(searchURL);
  doXml();
}

function displayError(error) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-success").style.display = "none";

  var displayErr = document.getElementById("wikindx-error");
  displayErr.innerHTML = error;
  displayErr.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}

function displaySuccess(success) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-error").style.display = "none";

  var displaySucc = document.getElementById("wikindx-success");
  displaySucc.innerHTML = success;
  displaySucc.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}

function doXml() {
  console.log('doXml(): ' + searchURL);
  xml.open("GET", searchURL, false);
  xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xml.send("format=json");
}

function prepareXml() {
  xml = new makeHttpObject();
  xml.onreadystatechange = function() {
    if( xml.readyState == 4 && xml.status == 200 ) {
      try {
        xmlResponse = JSON.parse(xml.responseText);
      }
      catch(e) {
        displayError(errorJSON);
        return false;
      }
    }
  };
  return true;
}

function userCheckHeartbeat() {
  if (heartbeat(document.getElementById("wikindx-url").value) !== true) {
    displayError(errorXMLHTTP);
    return false;
  }
  displaySuccess(successHeartbeat);
  return true;
}

function heartbeat(url) {
  xmlResponse = null;
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

function makeHttpObject() {
  try {return new XMLHttpRequest();}
  catch (error) {}
  try {return new ActiveXObject("Msxml2.XMLHTTP");}
  catch (error) {}
  try {return new ActiveXObject("Microsoft.XMLHTTP");}
  catch (error) {}
  throw new Error("Could not create HTTP request object.");
}

function insertParagraph() {
  Word.run(function (context) {

    var docBody = context.document.body;
    docBody.insertParagraph("Office has several versions, including Office 2016, Microsoft 365 subscription, and Office on the web!",
                            "Start");
    docBody.insertParagraph("Another paragraph",
                            "Start");

      return context.sync();
  })
  .catch(function (error) {
      console.log("Error: " + error);
      if (error instanceof OfficeExtension.Error) {
          console.log("Debug info: " + JSON.stringify(error.debugInfo));
      }
  });
}

function insertReference() {
  Word.run(function (context) {
    var hrReturn = heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return context.sync();
    }
    xmlResponse = null;
    getReference();
    if (xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return context.sync();
    }
//    bibEntry = xmlResponse.bibEntry;
    inTextReference = xmlResponse.inTextReference;
    storeID(xmlResponse.id);
    docSelection = context.document.getSelection();
    docSelection.insertHtml("&nbsp;" + inTextReference, "After");
//    docSelection.insertHtml(bibEntry, "End");
//    docSelection.insertBreak("Line", "After");
    return context.sync();
    })
  .catch(function (error) {
      console.log("Error: " + error);
      if (error instanceof OfficeExtension.Error) {
          console.log("Debug info: " + JSON.stringify(error.debugInfo));
          displayError(JSON.stringify(error));
          return false;
      }
  });
}

function insertCitation() {
  Word.run(function (context) {
    var hrReturn = heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return context.sync();
    }
    xmlResponse = null;
    getCitation();
    if (xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return context.sync();
    }
//    bibEntry = xmlResponse.bibEntry;
    inTextReference = xmlResponse.inTextReference;
    citation = xmlResponse.citation;
    storeID(xmlResponse.id);
    docSelection = context.document.getSelection();
    docSelection.insertHtml("&nbsp;" + citation + "&nbsp;" + inTextReference, "After");
//    docSelection.insertHtml(bibEntry, "End");
//    docSelection.insertBreak("Line", "After");
    return context.sync();
    })
  .catch(function (error) {
      console.log("Error: " + error);
      if (error instanceof OfficeExtension.Error) {
          console.log("Debug info: " + JSON.stringify(error.debugInfo));
          displayError(JSON.stringify(error));
          return false;
      }
  });
}

function storeID(id) {
  var office = Office.context.document;
  
  readIDs();
  Word.run(function (context) {
    if (Object.keys(storedIDs).length === 0) { // Nothing stored yet for this URL
      storedIDs[selectedURL] = [id];
    } else { // Need to append – to new WIKINDX URL or existing one?
      if (!(selectedURL in storedIDs)) { // new
        storedIDs[selectedURL] = [id]; 
      } else { // append – but check id does not already exist
        if (!storedIDs[selectedURL].includes(id)) {
          storedIDs[selectedURL].push(id);
        }
      }
    }
    office.settings.set('wikindx-id', storedIDs);
    office.settings.saveAsync(function (asyncResult) {
      if (asyncResult.status == Office.AsyncResultStatus.Failed) {
          console.log('Settings save failed. Error: ' + asyncResult.error.message);
      }
    });
    return context.sync();
  })
  .catch(function (error) {
      console.log("Error: " + error);
      if (error instanceof OfficeExtension.Error) {
          console.log("Debug info: " + JSON.stringify(error.debugInfo));
      }
  });
}

function readIDs() {
  var office = Office.context.document;

  Word.run(function (context) {
    if (office.settings.get('wikindx-id') === null) { // Nothing stored yet
      return context.sync();
    }
    storedIDs = office.settings.get('wikindx-id');
    return context.sync();
  })
  .catch(function (error) {
      console.log("Error: " + error);
      if (error instanceof OfficeExtension.Error) {
          console.log("Debug info: " + JSON.stringify(error.debugInfo));
      }
  });
}