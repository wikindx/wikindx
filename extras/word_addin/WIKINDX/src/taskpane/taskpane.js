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
var citation = '';
var id = '';
var hiddenID = '';
var errorAccess = 'The WIKINDX admin has not enabled read-only access.';
var errorXMLHTTP = "ERROR: XMLHTTP error – could not connect to the WIKINDX.  <br/><br/>There could be any number of reasons for this including an incorrect WIKINDX URL, an incompatibility between this add-in and the WIKINDX, the WIKINDX admin has not enabled read-only access, a network error . . .";
var errorStyles = "ERROR: Either no styles are defined on the selected WIKINDX or there are no available in-text citation styles.";
var errorSearch = "ERROR: Missing search input.";
var errorNewUrl = "ERROR: Missing URL or name input.";
var errorDuplicateUrl = "ERROR: Duplicate URL input.";
var errorDuplicateName = "ERROR: Duplicate name input.";
var errorNoResults = "No resources found matching your search.";
var errorJSON = "ERROR: Unspecified error. This could be any number of things from not being able to connect to the WIKINDX to no resources found matching your search.";
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

// Assign event handlers and other initialization logic.
    document.getElementById("wikindx-search").onclick = searchWikindx;
    document.getElementById("wikindx-url").onchange = styleSelectBox;
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
// For debugging only, uncomment. Otherwise, leave commented out. Uncommented, it will remove all stored URLs
 //     window.localStorage.removeItem('wikindx-localStorage');
//

// Check we have localStorage set up
    checkLocalStorage(false);
  }
});

function wikindxDisplayAbout() {
// Show About
  if (document.getElementById("wikindx-about").style.display == "none") {
    if (localStorage()) {
      document.getElementById("wikindx-about-begin").style.display = "none";
    } else {
      document.getElementById("wikindx-about-begin").style.display = "block";
    }
    document.getElementById("wikindx-about").style.display = "block";
    hideVisible(["wikindx-url-management", "wikindx-messages", "wikindx-display-results", "wikindx-search-parameters"]);
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
  var text = '<select class="selectStyle" id="wikindx-styleSelectBox">';

  for (i = 0; i < len; i++) {
    styleShort = xmlResponse[i].styleShort;
    styleLong = xmlResponse[i].styleLong;
    text += '<option value="' + styleShort + '">' + styleLong + '</option>';
  }
  text += '</select>';
  styles.innerHTML = text;
  styles.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "none";
  return true;
}

function searchWikindx() {
  var hrReturn = heartbeat(false);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }
  // Check a style is available
  if (document.getElementById("wikindx-search-results").style.display == "none") {
    styleSelectBox();
  }
  var searchText = document.getElementById("wikindx-search-text").value;
  searchText = searchText.trim();
  if (!searchText) {
    displayError(errorSearch);
    return false;
  }
  xmlResponse = null;
  getSearchInput(searchText);
  if (xmlResponse == null) {
    displayError(errorNoResults);
    return false;
  }
  printSearchResults();
  return true;
}

function printSearchResults() {
  var searchResults = document.getElementById("wikindx-search-results");
  var displayRef = document.getElementById("wikindx-display-ref");
  var len = xmlResponse.length;
  var initialRef, i;
  var text = '<select class="selectResults" id="wikindx-refSelectBox" onchange=displayReference()>';
  var hidden = '';

  for (i = 0; i < len; i++) {
    bibEntry = xmlResponse[i].bibEntry;
    id = xmlResponse[i].id;
    hiddenID = "hiddenRef" + id;
    text += '<option value="' + id + '">' + bibEntry + '</option>';
    hidden += '<input type="hidden" value="' + btoa(unescape(encodeURIComponent(bibEntry))) + '" id="' + hiddenID + '">';
    if (i == 0) {
      initialRef = bibEntry;
    }
  }
  text += '</select>';
  searchResults.innerHTML = text + hidden;
  displayRef.innerHTML = '<br/>' + initialRef;
  document.getElementById("wikindx-display-results").style.display = "block";
  document.getElementById("wikindx-messages").style.display = "none";
}

function getCitation() {
  var wikindxURL = document.getElementById("wikindx-url");
  var refSelectBox = document.getElementById("wikindx-refSelectBox");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getCitation' 
    + '&style=' + encodeURI(styleSelectBox.value) 
    + '&id=' + encodeURI(refSelectBox.value);
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

function getSearchInput(searchText) {
  searchText = searchText.replace(/[\u201C\u201D]/g, '"'); // Really!!!!! Ensure smart quotes are standard double quotes!!!!!
  var searchParams = document.getElementById("wikindx-search-params");
  var styleSelectBox = document.getElementById("wikindx-styleSelectBox");
  var wikindxURL = document.getElementById("wikindx-url");
  searchURL = wikindxURL.value 
    + "office.php" + '?method=getResources' 
    + '&searchWord=' + encodeURI(searchText)
    + '&style=' + encodeURI(styleSelectBox.value)
    + '&searchParams=' + encodeURI(searchParams.value);
  doXml();
}

function displayError(error) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-success").style.display = "none";

  var displayErr = document.getElementById("wikindx-error");
  displayErr.innerHTML = error;
  displayErr.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}

function displaySuccess(success) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-error").style.display = "none";

  var displaySucc = document.getElementById("wikindx-success");
  displaySucc.innerHTML = success;
  displaySucc.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}

function doXml() {
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

function insertCitation() {
  Word.run(function (context) {
    var hrReturn = heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return false;
    }
    xmlResponse = null;
    getCitation();
    bibEntry = xmlResponse.bibEntry;
    citation = xmlResponse.citation;
    storeID(xmlResponse.id);
    docSelection = context.document.getSelection();
    docSelection.insertHtml(citation, "After");
    docSelection.insertText(" ", "After");
    docSelection.insertHtml(bibEntry, "End");
    docSelection.insertBreak("Line", "After");
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
/** 
 * Comment out this line in production
*/
// office.settings.remove('wikindx-id');

  readIDs(office);

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

function readIDs(office) {
  Word.run(function (context) {
    if (office.settings.get('wikindx-id') === null) { // Nothing stored yet
      return context.sync();
    }
    storedIDs = office.settings.get('wikindx-id');
//    createCustomXmlPartAndStoreID();
    return context.sync();
  })
  .catch(function (error) {
      console.log("Error: " + error);
      if (error instanceof OfficeExtension.Error) {
          console.log("Debug info: " + JSON.stringify(error.debugInfo));
      }
  });
}