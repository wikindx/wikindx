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
var errorMissingID = "ERROR: Resource or citation ID not found in the selected WIKINDX.";
var errorNoInserts = "You have not inserted any references or citations yet so there is nothing to finalize.";
var successNewUrl = "Stored new WIKINDX: ";
var successEditUrl = "Edited WIKINDX URL.";
var successRemoveUrl = "Deleted WIKINDX URL(s).";
var successRemoveAllUrls = "Deleted all WIKINDX URLs.";
var successPreferredUrl = "Preference stored.";
var successHeartbeat = "Yes, I am alive and kicking. Try searching me . . .";
var xml;
var xmlResponse = null;
var docSelection;
var searchURL;
var visibleElements = [];
var selectedURL;
var numStoredURLs = 0;
var selectedName;
var wikindices = [];



/* global document, Office, Word */

Office.onReady(info => {

OfficeExtension.config.extendedErrorLogging = true;
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
    document.getElementById("wikindx-search").onclick = wikindxSearch;
    document.getElementById("wikindx-action").onchange = displayInit;
    document.getElementById("wikindx-finalize-run").onclick = finalizeRun;
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
    document.getElementById("wikindx-url-preferred").onclick = urlPreferredDisplay;
    document.getElementById("wikindx-url-delete").onclick = urlDeleteDisplay;
    document.getElementById("wikindx-display-about").onclick = wikindxDisplayAbout;
    document.getElementById("wikindx-url-heartbeat").onclick = userCheckHeartbeat;

//
// For debugging only, uncomment. Otherwise, leave commented out. Uncommented, it will remove all stored URLs from the Office environment. . .
//     window.localStorage.removeItem('wikindx-localStorage');
//

// Check we have localStorage set up
    checkLocalStorage(false);
    if (numStoredURLs) {
      document.getElementById("wikindx-action").style.display = "block";
    }
  }
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
// Before displaying the pane, check we have references and remove any empty wikindx-based custom contexts
  var found = false;
  var url;

  Word.run(async function (context) {
    var cc = context.document.contentControls.load();
    var split;
    await context.sync();
    for (var i = 0; i < cc.items.length; i++) {
      split = cc.items[i].tag.split('-');
      if ((split.length > 1) && (split[0] === 'wikindx') && (cc.items[i].text.trim() === '')) {
        cc.items[i].delete();
        continue;
      }
      // 'looking for wikindx-id-{[JSON string/array]}'
      if ((split.length < 3) 
        || (split[0] != 'wikindx')) {
          continue;
      }
      if (split[1] != 'id') {
        continue;
      }
      // If we get here, we have references
      found = true;
      url = JSON.parse(atob(split[2]))[0];
      if (!wikindices.includes(url)) {
        wikindices.push(url);
      }
    }
    if (!found) { // Nothing stored yet for this document
      displayError(errorNoInserts);
      return false;
    }
    // If we get here, there is something to finalize . . .
    finalizeGetStyles(wikindices);
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

function finalizeGetStyles(wikindices) {
  var finalStyles = new Object();
  var finalArray = [];
  var tempLongNames = new Object();
  var styleLong, styleShort, i, j, len, key;
  var text = '';
  for (i = 0; i < wikindices.length; i++) {
    var tempArray = [];
    xmlResponse = null;
    searchURL = wikindices[i] + "office.php" + '?method=getStyles';
    doXml();
    if (xmlResponse == null) {
      displayError(errorXMLHTTP);
      return false;
    }
    len = xmlResponse.length;
    if (!len) {
      displayError(errorStyles);
      return false;
    }
    // first run through – gather all styles from first WIKINDX
    if (!i) {
      for (j = 0; j < len; j++) {
        finalArray.push(xmlResponse[j].styleShort);
        tempLongNames[xmlResponse[j].styleShort] = xmlResponse[j].styleLong;
      }
      continue;
    } // else . . .
    for (j = 0; j < len; j++) {
      tempArray.push(xmlResponse[j].styleShort);
      tempLongNames[xmlResponse[j].styleShort] = xmlResponse[j].styleLong;
    }
    finalArray = finalArray.filter(value => tempArray.includes(value));
  }
 
  for (i = 0; i < finalArray.length; i++) {
    styleShort = finalArray[i];
    styleLong = tempLongNames[finalArray[i]];
    text += '<option value="' + styleShort + '">' + styleLong + '</option>';
  }
  document.getElementById("wikindx-finalize-styleSelectBox").innerHTML = text;
  return true;
}

function finalizeRun() {
  var finalizeReferences = [];
  var allItemObjects = [];
  var cleanIDs = new Object();
  var citeIDs = new Object();
  var foundBibliography = false;
  var item, split, urls, i, j, k, tag, cc, id, metaId;
  var bibliography = '';

  Word.run(async function (context) {
    cc = context.document.contentControls.load("items");
    await context.sync();
    for (i = 0; i < cc.items.length; i++) {
      if (cc.items[i].tag == 'wikindx-bibliography') {
        foundBibliography = true;
        continue;
      }
      split = cc.items[i].tag.split('-');
      if ((split.length < 3) // 'looking for wikindx-id-{[JSON string/array]}'
        || (split[0] != 'wikindx')) {
          continue;
      }
      if (split[1] != 'id') {
        continue;
      }
      item = JSON.parse(atob(split[2]));
      id = item[1];
      if (item.length == 3) { // citation
        metaId = item[2];
        if (!(item[0] in citeIDs)) {
          citeIDs[item[0]] = [metaId];
        } else if (!citeIDs[item[0]].includes(metaId)) {
            citeIDs[item[0]].push(metaId);
        }
      }
      if (!(item[0] in cleanIDs)) {
        cleanIDs[item[0]] = [id];
      } else if (!cleanIDs[item[0]].includes(id)) {
          cleanIDs[item[0]].push(id);
      }
    }
    // get references from WIKINDX
    urls = Object.keys(cleanIDs);
    for (i = 0; i < urls.length; i++) {
      finalizeGetReferences(urls[i], JSON.stringify(cleanIDs[urls[i]]));
  // Replace in-text references
      for (j = 0; j < xmlResponse.length; j++) {
        tag = 'wikindx-id-' + btoa(JSON.stringify([urls[i], xmlResponse[j].id]));
        cc = context.document.contentControls.getByTag(tag);
        cc.load('items');
        let items = {
          cc: cc,
          text: xmlResponse[j].inTextReference
        } 
        allItemObjects.push(items);
        if (!finalizeReferences.includes(xmlResponse[j].bibEntry)) {
          finalizeReferences.push(xmlResponse[j].bibEntry);
          bibliography += '<p>' + xmlResponse[j].bibEntry + '</p>';
        }
      }
    }
    await context.sync();
    for (j = 0; j < allItemObjects.length; j++) {
      let itemObject = allItemObjects[j];
      for (k = 0; k < itemObject.cc.items.length; k++) {
        let item = itemObject.cc.items[k];
        let itemText = itemObject.text;
        item.insertHtml(itemText, 'Replace');
      }
    }
    await context.sync();
    // get citation references from WIKINDX
    urls = Object.keys(citeIDs);
    for (i = 0; i < urls.length; i++) {
      finalizeGetCitations(urls[i], JSON.stringify(citeIDs[urls[i]]));
  // Replace in-text references
      allItemObjects = []; // Reset . . .
      for (j = 0; j < xmlResponse.length; j++) {
        tag = 'wikindx-id-' + btoa(JSON.stringify([urls[i], xmlResponse[j].id, xmlResponse[j].metaId]));
        cc = context.document.contentControls.getByTag(tag);
        cc.load('items');
        let items = {
          cc: cc,
          text: xmlResponse[j].inTextReference
        } 
        allItemObjects.push(items);
      }
    }
    await context.sync();
    for (j = 0; j < allItemObjects.length; j++) {
      let itemObject = allItemObjects[j];
      for (k = 0; k < itemObject.cc.items.length; k++) {
        let item = itemObject.cc.items[k];
        let itemText = itemObject.text;
        item.insertHtml(itemText, 'Replace');
      }
    }
    // Bibliography
    if (foundBibliography) {
      cc = context.document.contentControls.getByTag('wikindx-bibliography');
      cc.load('items');
      await context.sync();
      cc.items[0].insertHtml(bibliography, "Replace");
    } else {
      context.document.body.paragraphs.getLast().select("End");
      var sel = context.document.getSelection();
      sel.insertBreak("Line", "After");
      sel.insertBreak("Line", "After");
      context.document.body.paragraphs.getLast().select("End");
      sel = context.document.getSelection();
      cc = sel.insertContentControl();
      cc.color = 'orange';
      cc.tag = 'wikindx-bibliography';
      cc.title = 'Bibliography';
      cc.insertHtml(bibliography, "End");
    }
    return await context.sync();
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

function finalizeGetReferences(wikindxURL, ids) {
  xmlResponse = null;
  var searchParams = document.getElementById("wikindx-finalize-order");
  var styleSelectBox = document.getElementById("wikindx-finalize-styleSelectBox");
  searchURL = wikindxURL 
    + "office.php" + '?method=getBib' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&searchParams=' + encodeURI(searchParams.value)
    + '&ids=' + encodeURI(ids);
  doXml();
}

function finalizeGetCitations(wikindxURL, ids) {
  xmlResponse = null;
  var styleSelectBox = document.getElementById("wikindx-finalize-styleSelectBox");
  searchURL = wikindxURL 
    + "office.php" + '?method=getCiteCCs' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&ids=' + encodeURI(ids);
  doXml();
}

function displayReference() {
  xmlResponse = null;
  getReference();
  if (xmlResponse == 'Bad ID') {
    displayError(errorMissingID);
    return false;
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
    return false;
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
      document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Add WIKINDX';
      urlManagementDisplay("wikindx-url-entry");
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
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Edit WIKINDX';
  document.getElementById("wikindx-edit-url").value = selectedURL;
  document.getElementById("wikindx-edit-name").value = selectedName;
  urlManagementDisplay("wikindx-url-edit");
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
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Add WIKINDX';
  urlManagementDisplay("wikindx-url-entry");
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
  ++numStoredURLs;
  if (numStoredURLs > 1) {
    document.getElementById("wikindx-url-preferred").style.display = "block";
  }
  window.localStorage.setItem('wikindx-localStorage', JSON.stringify(jsonArray));
  getUrlSelectBox(jsonArray);
  visibleElements.push("wikindx-search-parameters");
  visibleElements.push("wikindx-action");
  retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  styleSelectBox(); // Preload with first value from wikindx-url select box
  displaySuccess(successNewUrl + name + ' (' + url + ')');
  return;
}

function urlManagementDisplay(turnOn) {
  var displays = ["wikindx-url-entry", "wikindx-url-edit", "wikindx-urls-remove", "wikindx-urls-preferred"];
  for (var i = 0; i < displays.length; i++) {
    if (displays[i] != turnOn) {
      document.getElementById(displays[i]).style.display = "none";
    }
  }

  hideVisible(["wikindx-search-parameters", "wikindx-display-results"]);
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById(turnOn).style.display = "block";
  document.getElementById("wikindx-url-management").style.display = "block";
}

function urlPreferredDisplay() {
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var len = jsonArray.length;
  var i, id;
  var text = '';
  var checked = 'checked';
  for (i = 0; i < len; i++) {
    if (i) {
      checked = '';
    }
    id = jsonArray[i][1]; // The WIKINDX name
    text += '<input type="radio" id="' + id + '" name="wikindx-preferred" value="' + id + '"' + checked + '>' 
      + '<label for="' + id + '"> ' + jsonArray[i][1] + ': ' + jsonArray[i][0] + '</label><br/>';
  }
  text += '<button class="button" id="wikindx-url-prefer" alt="Set preferred WIKINDX" title="Set preferred WIKINDX">Store</button>';
  text += '<button class="button" id="wikindx-close-url-preferred" alt="Close" title="Close">Close</button>';
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Preferred WIKINDX';
  document.getElementById("wikindx-urls-preferred").innerHTML = text;
  urlManagementDisplay("wikindx-urls-preferred");
  document.getElementById("wikindx-url-prefer").onclick = urlPrefer;
  document.getElementById("wikindx-close-url-preferred").onclick = wikindxClose;
}

function urlPrefer() {
  // What is in position [0] of the array is the preferrred URL . . .
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var name = document.querySelector('input[name="wikindx-preferred"]:checked').value;
  var newArray = [];
  var zeroth = [];

  if (name != jsonArray[0][1]) { // Selected value not yet in position 0
    zeroth = jsonArray[0];
    for (var i = 1; i < jsonArray.length; i++) {
      if (name == jsonArray[i][1]) {
        newArray[0] = jsonArray[i];
      } else {
        newArray[i] = jsonArray[i];
      }
    }
    newArray.push(zeroth);
    window.localStorage.setItem('wikindx-localStorage', JSON.stringify(newArray));
    getUrlSelectBox(newArray);
    styleSelectBox();
  }
  retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  document.getElementById("wikindx-search-parameters").style.display = "block";
  displaySuccess(successPreferredUrl);
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
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Delete WIKINDX';
  document.getElementById("wikindx-urls-remove").innerHTML = text;
  urlManagementDisplay("wikindx-urls-remove");
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

  // First compare
  for (i = 0; i < len; i++) {
      id = jsonArray[i][0] + '--WIKINDX--' + jsonArray[i][1];
      if (document.getElementById(id).checked == false){
        split = id.split('--WIKINDX--');
        keep.push([split[0], split[1]]);
      } else {
          ++removeCount;
          --numStoredURLs;
      }
  }
  if (!removeCount) {
    urlDeleteDisplay();
    return;
  }
  if (numStoredURLs < 2) {
    document.getElementById("wikindx-url-preferred").style.display = "none";
  }
  if (removeCount == len) { // Have we completely emptied the list?
    window.localStorage.removeItem('wikindx-localStorage');
    document.getElementById("wikindx-about-begin").style.display = "block";
    document.getElementById("wikindx-urls-remove").style.display = "none";
    document.getElementById("wikindx-action").style.display = "none";
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
    if (window.localStorage.getItem('wikindx-localStorage') == null) {
        return false;
    }
    var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
    numStoredURLs = jsonArray.length;
    if (numStoredURLs > 1) {
      document.getElementById("wikindx-url-preferred").style.display = "block";
    }
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

function insertReference() {
  Word.run(async function (context) {
    var hrReturn = heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return await context.sync();
    }
    xmlResponse = null;
    getReference();
    if (xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return await context.sync();
    }
    bibEntry = xmlResponse.bibEntry;
    inTextReference = xmlResponse.inTextReference;
    docSelection = context.document.getSelection();
    var cc = docSelection.insertContentControl();
    cc.color = 'orange';
    cc.tag = 'wikindx-id-' + btoa(JSON.stringify([selectedURL, xmlResponse.id]));
    cc.title = HtmlEntities.decode(bibEntry.replace(/<[^>]*>?/gm, '')); // contextControl title doesn't accept HTML
    cc.insertHtml(inTextReference, "Replace");
    await context.sync();
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
  Word.run(async function (context) {
    var hrReturn = heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return await context.sync();
    }
    xmlResponse = null;
    getCitation();
    if (xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return await context.sync();
    }
    bibEntry = xmlResponse.bibEntry;
    inTextReference = xmlResponse.inTextReference;
    citation = xmlResponse.citation;
    docSelection = context.document.getSelection();
    var cc = docSelection.insertContentControl();
    cc.color = 'orange';
    cc.tag = 'wikindx-id-' + btoa(JSON.stringify([selectedURL, xmlResponse.id, xmlResponse.metaId]));
    cc.title = HtmlEntities.decode(bibEntry.replace(/<[^>]*>?/gm, '')); // contextControl title doesn't accept HTML
    cc.insertHtml(inTextReference, "Replace");
 //   await context.sync();
    var ccRange = cc.getRange('Whole');
    ccRange.insertHtml(citation + '&nbsp;', "Before");
    await context.sync();
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

/**
 * HtmlEntities code pinched/borrowed from https://jsfiddle.net/BideoWego/muuvvof8/
 */
var HtmlEntities = function() {};

HtmlEntities.map = {
    "'": "&apos;",
    "<": "&lt;",
    ">": "&gt;",
    " ": "&nbsp;",
    "¡": "&iexcl;",
    "¢": "&cent;",
    "£": "&pound;",
    "¤": "&curren;",
    "¥": "&yen;",
    "¦": "&brvbar;",
    "§": "&sect;",
    "¨": "&uml;",
    "©": "&copy;",
    "ª": "&ordf;",
    "«": "&laquo;",
    "¬": "&not;",
    "®": "&reg;",
    "¯": "&macr;",
    "°": "&deg;",
    "±": "&plusmn;",
    "²": "&sup2;",
    "³": "&sup3;",
    "´": "&acute;",
    "µ": "&micro;",
    "¶": "&para;",
    "·": "&middot;",
    "¸": "&cedil;",
    "¹": "&sup1;",
    "º": "&ordm;",
    "»": "&raquo;",
    "¼": "&frac14;",
    "½": "&frac12;",
    "¾": "&frac34;",
    "¿": "&iquest;",
    "À": "&Agrave;",
    "Á": "&Aacute;",
    "Â": "&Acirc;",
    "Ã": "&Atilde;",
    "Ä": "&Auml;",
    "Å": "&Aring;",
    "Æ": "&AElig;",
    "Ç": "&Ccedil;",
    "È": "&Egrave;",
    "É": "&Eacute;",
    "Ê": "&Ecirc;",
    "Ë": "&Euml;",
    "Ì": "&Igrave;",
    "Í": "&Iacute;",
    "Î": "&Icirc;",
    "Ï": "&Iuml;",
    "Ð": "&ETH;",
    "Ñ": "&Ntilde;",
    "Ò": "&Ograve;",
    "Ó": "&Oacute;",
    "Ô": "&Ocirc;",
    "Õ": "&Otilde;",
    "Ö": "&Ouml;",
    "×": "&times;",
    "Ø": "&Oslash;",
    "Ù": "&Ugrave;",
    "Ú": "&Uacute;",
    "Û": "&Ucirc;",
    "Ü": "&Uuml;",
    "Ý": "&Yacute;",
    "Þ": "&THORN;",
    "ß": "&szlig;",
    "à": "&agrave;",
    "á": "&aacute;",
    "â": "&acirc;",
    "ã": "&atilde;",
    "ä": "&auml;",
    "å": "&aring;",
    "æ": "&aelig;",
    "ç": "&ccedil;",
    "è": "&egrave;",
    "é": "&eacute;",
    "ê": "&ecirc;",
    "ë": "&euml;",
    "ì": "&igrave;",
    "í": "&iacute;",
    "î": "&icirc;",
    "ï": "&iuml;",
    "ð": "&eth;",
    "ñ": "&ntilde;",
    "ò": "&ograve;",
    "ó": "&oacute;",
    "ô": "&ocirc;",
    "õ": "&otilde;",
    "ö": "&ouml;",
    "÷": "&divide;",
    "ø": "&oslash;",
    "ù": "&ugrave;",
    "ú": "&uacute;",
    "û": "&ucirc;",
    "ü": "&uuml;",
    "ý": "&yacute;",
    "þ": "&thorn;",
    "ÿ": "&yuml;",
    "Œ": "&OElig;",
    "œ": "&oelig;",
    "Š": "&Scaron;",
    "š": "&scaron;",
    "Ÿ": "&Yuml;",
    "ƒ": "&fnof;",
    "ˆ": "&circ;",
    "˜": "&tilde;",
    "Α": "&Alpha;",
    "Β": "&Beta;",
    "Γ": "&Gamma;",
    "Δ": "&Delta;",
    "Ε": "&Epsilon;",
    "Ζ": "&Zeta;",
    "Η": "&Eta;",
    "Θ": "&Theta;",
    "Ι": "&Iota;",
    "Κ": "&Kappa;",
    "Λ": "&Lambda;",
    "Μ": "&Mu;",
    "Ν": "&Nu;",
    "Ξ": "&Xi;",
    "Ο": "&Omicron;",
    "Π": "&Pi;",
    "Ρ": "&Rho;",
    "Σ": "&Sigma;",
    "Τ": "&Tau;",
    "Υ": "&Upsilon;",
    "Φ": "&Phi;",
    "Χ": "&Chi;",
    "Ψ": "&Psi;",
    "Ω": "&Omega;",
    "α": "&alpha;",
    "β": "&beta;",
    "γ": "&gamma;",
    "δ": "&delta;",
    "ε": "&epsilon;",
    "ζ": "&zeta;",
    "η": "&eta;",
    "θ": "&theta;",
    "ι": "&iota;",
    "κ": "&kappa;",
    "λ": "&lambda;",
    "μ": "&mu;",
    "ν": "&nu;",
    "ξ": "&xi;",
    "ο": "&omicron;",
    "π": "&pi;",
    "ρ": "&rho;",
    "ς": "&sigmaf;",
    "σ": "&sigma;",
    "τ": "&tau;",
    "υ": "&upsilon;",
    "φ": "&phi;",
    "χ": "&chi;",
    "ψ": "&psi;",
    "ω": "&omega;",
    "ϑ": "&thetasym;",
    "ϒ": "&Upsih;",
    "ϖ": "&piv;",
    "–": "&ndash;",
    "—": "&mdash;",
    "‘": "&lsquo;",
    "’": "&rsquo;",
    "‚": "&sbquo;",
    "“": "&ldquo;",
    "”": "&rdquo;",
    "„": "&bdquo;",
    "†": "&dagger;",
    "‡": "&Dagger;",
    "•": "&bull;",
    "…": "&hellip;",
    "‰": "&permil;",
    "′": "&prime;",
    "″": "&Prime;",
    "‹": "&lsaquo;",
    "›": "&rsaquo;",
    "‾": "&oline;",
    "⁄": "&frasl;",
    "€": "&euro;",
    "ℑ": "&image;",
    "℘": "&weierp;",
    "ℜ": "&real;",
    "™": "&trade;",
    "ℵ": "&alefsym;",
    "←": "&larr;",
    "↑": "&uarr;",
    "→": "&rarr;",
    "↓": "&darr;",
    "↔": "&harr;",
    "↵": "&crarr;",
    "⇐": "&lArr;",
    "⇑": "&UArr;",
    "⇒": "&rArr;",
    "⇓": "&dArr;",
    "⇔": "&hArr;",
    "∀": "&forall;",
    "∂": "&part;",
    "∃": "&exist;",
    "∅": "&empty;",
    "∇": "&nabla;",
    "∈": "&isin;",
    "∉": "&notin;",
    "∋": "&ni;",
    "∏": "&prod;",
    "∑": "&sum;",
    "−": "&minus;",
    "∗": "&lowast;",
    "√": "&radic;",
    "∝": "&prop;",
    "∞": "&infin;",
    "∠": "&ang;",
    "∧": "&and;",
    "∨": "&or;",
    "∩": "&cap;",
    "∪": "&cup;",
    "∫": "&int;",
    "∴": "&there4;",
    "∼": "&sim;",
    "≅": "&cong;",
    "≈": "&asymp;",
    "≠": "&ne;",
    "≡": "&equiv;",
    "≤": "&le;",
    "≥": "&ge;",
    "⊂": "&sub;",
    "⊃": "&sup;",
    "⊄": "&nsub;",
    "⊆": "&sube;",
    "⊇": "&supe;",
    "⊕": "&oplus;",
    "⊗": "&otimes;",
    "⊥": "&perp;",
    "⋅": "&sdot;",
    "⌈": "&lceil;",
    "⌉": "&rceil;",
    "⌊": "&lfloor;",
    "⌋": "&rfloor;",
    "⟨": "&lang;",
    "⟩": "&rang;",
    "◊": "&loz;",
    "♠": "&spades;",
    "♣": "&clubs;",
    "♥": "&hearts;",
    "♦": "&diams;"
};

HtmlEntities.decode = function(string) {
    var entityMap = HtmlEntities.map;
    for (var key in entityMap) {
        var entity = entityMap[key];
        var regex = new RegExp(entity, 'g');
        string = string.replace(regex, key);
    }
    string = string.replace(/&quot;/g, '"');
    string = string.replace(/&amp;/g, '&');
    return string;
}

HtmlEntities.encode = function(string) {
    var entityMap = HtmlEntities.map;
    string = string.replace(/&/g, '&amp;');
    string = string.replace(/"/g, '&quot;');
    for (var key in entityMap) {
        var entity = entityMap[key];
        var regex = new RegExp(key, 'g');
        string = string.replace(regex, entity);
    }
    return string;
}
