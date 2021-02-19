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
import { HtmlEntities } from "./HtmlEntities";
import * as Xml from "./wikindxXml";
import * as LocalStorage from "./wikindxLocalStorage";
import * as Styles from "./wikindxStyles";
import * as UrlManagement from "./wikindxUrlManagement";
//import { testPromise } from "./testPromise";

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
var errorNoInserts = "You have not inserted any references or citations yet so there is nothing to finalize.";
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
    document.getElementById("wikindx-finalize-run").onclick = finalizeRun;
    document.getElementById("wikindx-url").onchange = Styles.styleSelectBox;
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
    Visible.displayCitationPane();
  } else if (document.getElementById("wikindx-action").value == 'finalize') {
    Visible.displayFinalizePane();
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
      if (!Styles.wikindices.includes(url)) {
        Styles.wikindicesPush(url);
      }
    }
    if (!found) { // Nothing stored yet for this document
      displayError(errorNoInserts);
      return false;
    }
    // If we get here, there is something to finalize . . .
    if (Styles.finalizeGetStyles()) {
    document.getElementById("wikindx-finalize").style.display = "block";
    }
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

function finalizeRun() {
  var finalizeReferences = [];
  var allItemObjects = [];
  var cleanIDs = new Object();
  var citeIDs = new Object();
  var foundBibliography = false;
  var item, split, urls, i, j, k, tag, cc, id, metaId, multipleWikindices, key;
  var bibliography = '';

  document.getElementById("wikindx-finalize-working").style.display = "block";
  document.getElementById("wikindx-finalize-completed").style.display = "none";

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
    multipleWikindices = false;
    if (urls.length > 1) {
      multipleWikindices = true;
      var params = document.getElementById("wikindx-finalize-order").value;
      split = params.split('_');
      var order = split[0];
      var ascDesc = split[1];
      var multipleOrder = [];
    }
    for (i = 0; i < urls.length; i++) {
      Xml.finalizeGetReferences(urls[i], JSON.stringify(cleanIDs[urls[i]]));
  // Replace in-text references
      for (j = 0; j < Xml.xmlResponse.length; j++) {
        tag = 'wikindx-id-' + btoa(JSON.stringify([urls[i], Xml.xmlResponse[j].id]));
        cc = context.document.contentControls.getByTag(tag);
        cc.load('items');
        let items = {
          cc: cc,
          text: Xml.xmlResponse[j].inTextReference
        } 
        allItemObjects.push(items);console.table(Xml.xmlResponse[j]);
        if (!finalizeReferences.includes(Xml.xmlResponse[j].bibEntry)) {
          finalizeReferences.push(Xml.xmlResponse[j].bibEntry);
          if (multipleWikindices) {
            key = finalizeReferences.indexOf(Xml.xmlResponse[j].bibEntry);
            multipleOrder.push({
              "index": key,
              "creator": Xml.xmlResponse[j].creatorOrder, 
              "title": Xml.xmlResponse[j].titleOrder,
              "year": Xml.xmlResponse[j].yearOrder
            });
          }
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
      Xml.finalizeGetCitations(urls[i], JSON.stringify(citeIDs[urls[i]]));
  // Replace in-text references
      allItemObjects = []; // Reset . . .
      for (j = 0; j < Xml.xmlResponse.length; j++) {
        tag = 'wikindx-id-' + btoa(JSON.stringify([urls[i], Xml.xmlResponse[j].id, Xml.xmlResponse[j].metaId]));
        cc = context.document.contentControls.getByTag(tag);
        cc.load('items');
        let items = {
          cc: cc,
          text: Xml.xmlResponse[j].inTextReference
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
    console.table(multipleOrder);
    if (!multipleWikindices) {
      for (i = 0; i < finalizeReferences.length; i++) {
        bibliography += '<p>' + finalizeReferences[i] + '</p>';
      }
    }
    if (!multipleWikindices) {
      for (i = 0; i < finalizeReferences.length; i++) {
        bibliography += '<p>' + finalizeReferences[i] + '</p>';
      }
    } else {
      if (order == 'creator') {
        multipleOrder.sort(function (a, b) {
          return a.creator.localeCompare(b.creator) || a.year - b.year || a.title.localeCompare(b.title);
        })
      }
      else if (order == 'title') {
        multipleOrder.sort(function (a, b) {
          return a.title.localeCompare(b.title) || a.creator.localeCompare(b.creator) || a.year - b.year;
        })
      } else { // year
        multipleOrder.sort(function (a, b) {
          return a.year - b.year || a.creator.localeCompare(b.creator) || a.title.localeCompare(b.title);
        })
      }
      if (ascDesc == 'DESC') {
          multipleOrder.reverse();
      }
      console.table(finalizeReferences);
      for (i = 0; i < multipleOrder.length; i++) {
        key = multipleOrder[i].index;
        bibliography += '<p>' + finalizeReferences[key] + '</p>';
      }
    }
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
      await context.sync();
    }
  })
  .then(function() {
    document.getElementById("wikindx-finalize-working").style.display = "none";
    document.getElementById("wikindx-finalize-completed").style.display = "block";
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

async function wikindxSearch() {
  var hrReturn = Xml.heartbeat(false);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }
  var searchText = document.getElementById("wikindx-search-text").value;
  searchText = searchText.trim();
  searchText = searchText.replace(/[\u201C\u201D]/g, '"'); // Really!!!!! Ensure smart quotes are standard double quotes!!!!!
  if (!searchText) {
    displayError(errorSearch);
    return false;
  }
  if (document.getElementById("wikindx-action").value == 'references') {
    await search('references', searchText);
  } else if (document.getElementById("wikindx-action").value == 'citations') {
    search('citations', searchText);
  }
}
/**
 * After DAYS of trying, this is the best I can do.... TODO - remove the sleep promise thingamijig and still have it working!
 * 
 */
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function search(type, searchText) {
  document.getElementById("wikindx-search-completed").style.display = "none";
  document.getElementById("wikindx-search-working").style.display = "block"; 
  await sleep(13); // seems to be the smallest value possible . . .
  if (type == 'references') {
    searchReferences(searchText);
  } else {
    searchCitations(searchText);
  }
  document.getElementById("wikindx-search-working").style.display = "none";
  document.getElementById("wikindx-search-completed").style.display = "block";
}

function searchCitations(searchText) {
  Xml.getSearchInputCitations(searchText);
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
    var hrReturn = Xml.heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return;
    }
    getReference();
    if (Xml.xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return;
    }
    bibEntry = Xml.xmlResponse.bibEntry;
    inTextReference = Xml.xmlResponse.inTextReference;
    docSelection = context.document.getSelection();
    var cc = docSelection.insertContentControl();
    cc.color = 'orange';
    cc.tag = 'wikindx-id-' + btoa(JSON.stringify([Xml.selectedURL, Xml.xmlResponse.id]));
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
    var hrReturn = Xml.heartbeat(false);
    if (hrReturn !== true) {
      displayError(hrReturn);
      return ;
    }
    getCitation();
    if (Xml.xmlResponse == 'Bad ID') {
      displayError(errorMissingID);
      return;
    }
    bibEntry = Xml.xmlResponse.bibEntry;
    inTextReference = Xml.xmlResponse.inTextReference;
    citation = Xml.xmlResponse.citation;
    docSelection = context.document.getSelection();
    context.load(docSelection, 'font/size, font/name, font/color');
    await context.sync();
    var cc = docSelection.insertContentControl();
    cc.color = 'orange';
    cc.tag = 'wikindx-id-' + btoa(JSON.stringify([Xml.selectedURL, Xml.xmlResponse.id, Xml.xmlResponse.metaId]));
    cc.title = HtmlEntities.decode(bibEntry.replace(/<[^>]*>?/gm, '')); // contextControl title doesn't accept HTML
    cc.insertHtml(inTextReference, "Replace");
    var ccRange = cc.getRange('Whole');
    ccRange.insertHtml(citation + '&nbsp;', "Before").font.set({
      size: docSelection.font.size,
      color: docSelection.font.color,
      name: docSelection.font.name
    });
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
