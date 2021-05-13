/*
 * Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 * See LICENSE in the project root for license information.
 */

// images references in the manifest
import "../../assets/wikindx-16.png";
import "../../assets/wikindx-32.png";
import "../../assets/wikindx-80.png";
import { displayError } from "./wikindxMessages";
import * as Visible from "./wikindxVisible";
import * as Help from "./wikindxDisplayHelp";
import * as Xml from "./wikindxXml";
import * as LocalStorage from "./wikindxLocalStorage";
import * as Styles from "./wikindxStyles";
import * as UrlManagement from "./wikindxUrlManagement";
import * as Finalize from "./wikindxFinalize";

/* global vars */
var bibEntry = '';
var inTextReference = '';
var citation = '';
var id = '';
var initialId = false;
var errorSearch = "ERROR: Missing search input.";
var errorNoResultsReferences = "No references found matching your search.";
var errorNoResultsCitations = "No citations found matching your search.";
// var errorXMLTimeout = "ERROR: The search timed out. Consider reformulating your search."
var errorMissingID = "ERROR: Resource or citation ID not found in the selected WIKINDX.";
var docSelection;


/* global document, Office, Word */

Office.onReady(info => {

OfficeExtension.config.extendedErrorLogging = true;
  if (info.host === Office.HostType.Word) {
  // Determine if the user's version of Office supports all the Office.js APIs that are used in the tutorial.
    if (!Office.context.requirements.isSetSupported('WordApi', '1.3')) {
        console.log('Sorry. The WIKINDX citation tool uses Word.js APIs that are not available in your version of Office.');
    }
  
// Prepare for XMLHTTP connections
    if (Xml.prepareXml() == false) {
      displayError(Xml.errorXMLHTTP);
      return false;
    };

/** 
 * Comment out this line in production. Uncommented, it removes all persistent settings in the open document . . .
*/
// Office.context.document.settings.remove('wikindx-id');

// Assign event handlers and other initialization logic.
    document.getElementById("wikindx-search").onclick = wikindxSearch;
    document.getElementById("wikindx-action").onchange = displayInit;
    document.getElementById("wikindx-finalize-run").onclick = Finalize.finalizeRun;
    document.getElementById("wikindx-url").onchange = 
      function () {Styles.styleSelectBox(), Xml.citationCreatorsSelectBox() };
    document.getElementById("wikindx-styleSelectBox").onchange = Visible.reset;
    document.getElementById("wikindx-reference-params").onchange = Visible.reset;
    document.getElementById("wikindx-citation-params").onchange = Visible.reset;
    document.getElementById("wikindx-refSelectBox").onchange = displayReference;
    document.getElementById("wikindx-citeSelectBox").onchange = displayCitation;
    document.getElementById("wikindx-insert-reference").onclick = insertReference;
    document.getElementById("wikindx-insert-citation").onclick = insertCitation;
    document.getElementById("wikindx-url-store").onclick = UrlManagement.urlRegister;
    document.getElementById("wikindx-url-add").onclick = UrlManagement.urlAddDisplay;
    document.getElementById("wikindx-url-edit1").onclick = UrlManagement.urlEditDisplay;
    document.getElementById("wikindx-url-edit2").onclick = UrlManagement.urlEdit;
    document.getElementById("wikindx-close-url-entry").onclick = UrlManagement.wikindxClose;
    document.getElementById("wikindx-close-url-edit").onclick = UrlManagement.wikindxClose;
    document.getElementById("wikindx-url-preferred").onclick = UrlManagement.urlPreferredDisplay;
    document.getElementById("wikindx-url-delete").onclick = UrlManagement.urlDeleteDisplay;
    document.getElementById("wikindx-display-about").onclick = Help.wikindxDisplayAbout;
    document.getElementById("wikindx-display-references-help").onclick = Help.wikindxDisplayReferencesHelp;
    document.getElementById("wikindx-display-citations-help").onclick = Help.wikindxDisplayCitationsHelp;
    document.getElementById("wikindx-display-finalize-help").onclick = Help.wikindxDisplayFinalizeHelp;
    document.getElementById("wikindx-url-heartbeat").onclick = Xml.userCheckHeartbeat;

//
// For debugging only, uncomment. Otherwise, leave commented out. Uncommented, it will remove all stored URLs from the Office environment. . .
//     window.localStorage.removeItem('wikindx-localStorage');
//

// Check we have localStorage set up
    LocalStorage.checkLocalStorage(false);
    if (LocalStorage.numStoredURLs) {
      document.getElementById("wikindx-action").style.display = "block";
    }
  }
});

function displayInit() {
  document.getElementById("wikindx-about").style.display = "none";
  document.getElementById("wikindx-display-about").src = "../../assets/lightbulb_off.png";
  if (document.getElementById("wikindx-action").value == 'references') {
    Visible.displayReferencePane();
  } else if (document.getElementById("wikindx-action").value == 'citations') {
    Xml.citationCreatorsSelectBox();
    Visible.displayCitationPane();
  } else if (document.getElementById("wikindx-action").value == 'finalize') {
    Visible.displayFinalizePane();
    Finalize.finalizeDisplay();
  }
}

async function wikindxSearch() {
/*  var hrReturn = Xml.heartbeat(false);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }
*/
  var searchText = document.getElementById("wikindx-search-text").value;
  var andOr = 'AND';
  searchText = searchText.trim();
  searchText = searchText.replace(/[\u201C\u201D]/g, '"'); // Really!!!!! Ensure smart quotes are standard double quotes!!!!!
  if (document.getElementById("wikindx-action").value == 'citations') {
    if (document.getElementById("wikindx-citations-or").checked) {
        andOr = 'OR';
    }
    var creator = document.getElementById("wikindx-creatorsSelectBox").value;
    if (!searchText && !creator) {
      displayError(errorSearch);
      return false;
    }
  } else if (!searchText) {
    displayError(errorSearch);
    return false;
  }
  if (document.getElementById("wikindx-action").value == 'references') {
    await search('references', searchText, false, false);
  } else if (document.getElementById("wikindx-action").value == 'citations') {
    await search('citations', searchText, andOr, creator);
  }
}
/**
 * After DAYS of trying, this is the best I can do.... TODO - remove the sleep promise thingamijig and still have it working!
 * 
 */
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function search(type, searchText, andOr, creator) {
  document.getElementById("wikindx-search-completed").style.display = "none";
  document.getElementById("wikindx-search-working").style.display = "block"; 
  await sleep(13); // seems to be the smallest value possible . . .
  if (type == 'references') {
    searchReferences(searchText);
  } else {
    searchCitations(searchText, andOr, creator);
  }
  document.getElementById("wikindx-search-working").style.display = "none";
  document.getElementById("wikindx-search-completed").style.display = "block";
}

function searchCitations(searchText, andOr, creator) {
  Xml.getSearchInputCitations(searchText, andOr, creator);
  if (Xml.xmlResponse == null) {
    displayError(errorNoResultsCitations);
    return false;
  }
  printSearchResultsCitations();
  return true;
}

function searchReferences(searchText) {
  Xml.getSearchInputReferences(searchText);
  if (Xml.xmlResponse == null) {
    displayError(errorNoResultsReferences);
    return false;
  }
  printSearchResultsReferences();
  return true;
}

function printSearchResultsReferences() {
  var refSelectBox = document.getElementById("wikindx-refSelectBox");
  var len = Xml.xmlResponse.length;
  var i;
  var text = '';

  for (i = 0; i < len; i++) {
    bibEntry = Xml.xmlResponse[i].bibEntry;
    id = Xml.xmlResponse[i].id;
    text += '<option value="' + id + '">' + bibEntry + '</option>';
    if (i == 0) {
      initialId = id;
    }
  }
  displayReference();
  refSelectBox.innerHTML = text;
  Visible.endPrintReference();
}

function printSearchResultsCitations() {
  var citeSelectBox = document.getElementById("wikindx-citeSelectBox");
  var len = Xml.xmlResponse.length;
  var i;
  var text = '';

  for (i = 0; i < len; i++) {
    citation = Xml.xmlResponse[i].citation;
    id = Xml.xmlResponse[i].id;
    text += '<option value="' + id + '">' + citation + '</option>';
    if (i == 0) {
      initialId = id;
    }
  }
  displayCitation();
  citeSelectBox.innerHTML = text;
  Visible.endPrintCitation();
}

function displayReference() {
  getReference();
  if (Xml.xmlResponse == 'Bad ID') {
    displayError(errorMissingID);
    return false;
  }
  bibEntry = Xml.xmlResponse.bibEntry;
  document.getElementById("wikindx-display-ref").innerHTML = '</br>' + bibEntry;
  return true;
}

function displayCitation() {
  getCitation();
  if (Xml.xmlResponse == 'Bad ID') {
    displayError(errorMissingID);
    return false;
  }
  citation = Xml.xmlResponse.citation;
  bibEntry = Xml.xmlResponse.bibEntry;
  document.getElementById("wikindx-display-cite").innerHTML = '</br>' + citation + '<br/><br/>' + bibEntry;
  return true;
}

function getReference() {
  if (!initialId) {
    var id = document.getElementById("wikindx-refSelectBox").value;
  } else {
    var id = initialId;
    initialId = false;
  }
  Xml.getReference(id);
}

function getCitation() {
  if (!initialId) {
    var id = document.getElementById("wikindx-citeSelectBox").value;
  } else {
    var id = initialId;
    initialId = false;
  }
  Xml.getCitation(id);
}

function insertReference() {
  Word.run(async function (context) {
/*    var hrReturn = Xml.heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return;
    }
*/    getReference();
    if (Xml.xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return;
    }
    inTextReference = Xml.xmlResponse.inTextReference;
    docSelection = context.document.getSelection();
    var cc = docSelection.insertContentControl();
    cc.color = 'orange';
    cc.tag = 'wikindx-id-' + btoa(JSON.stringify([Xml.selectedURL, Xml.xmlResponse.id]));
    cc.title = Xml.xmlResponse.titleCC;
    console.log(Xml.xmlResponse.titleCC);
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
/*    var hrReturn = Xml.heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return ;
    }
*/    getCitation();
    if (Xml.xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return;
    }
    inTextReference = Xml.xmlResponse.inTextReference;
    citation = Xml.xmlResponse.citation;
    docSelection = context.document.getSelection();
    context.load(docSelection, 'font/size, font/name, font/color');
    await context.sync();
    var cc = docSelection.insertContentControl();
    cc.color = 'orange';
    cc.tag = 'wikindx-id-' + btoa(JSON.stringify([Xml.selectedURL, Xml.xmlResponse.id, Xml.xmlResponse.metaId]));
    cc.title = Xml.xmlResponse.titleCC;
    cc.insertHtml(inTextReference, "Replace");
    var ccRange = cc.getRange('Whole');
    if (document.getElementById("wikindx-citation-html").checked) {
      ccRange.insertHtml(citation + '&nbsp;', "Before").font.set({
        size: docSelection.font.size,
        color: docSelection.font.color,
        name: docSelection.font.name
      });
    } else {
      ccRange.insertHtml(citation + '&nbsp;', "Before");
    }
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
