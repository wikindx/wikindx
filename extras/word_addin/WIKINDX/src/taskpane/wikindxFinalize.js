import { displayError } from "./wikindxMessages";
import * as Xml from "./wikindxXml";
import * as Styles from "./wikindxStyles";

var order, ascDesc, id;
var foundBibliography;
var bibliography;
var finalizeReferences = [];
var allItemObjects = [];
var multipleOrder = [];
var multipleWikindices;
var cleanIDs = new Object();
var citeIDs = new Object();
var errorNoInserts = "You have not inserted any references or citations yet so there is nothing to finalize.";

export function finalizeDisplay() {
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
export function finalizeRun() {
  var split, urls, i, j, k, cc;
  foundBibliography = false;
  bibliography = '';
  finalizeReferences = [];
  allItemObjects = [];
  multipleOrder = [];
  cleanIDs = new Object();
  citeIDs = new Object();

  document.getElementById("wikindx-finalize-working").style.display = "block";
  document.getElementById("wikindx-finalize-completed").style.display = "none";

  Word.run(async function (context) {
    cc = context.document.contentControls.load("items");
    await context.sync();
    getCleanIDs(cc);
    // get references from WIKINDX
    urls = Object.keys(cleanIDs);
    multipleWikindices = false;
    if (urls.length > 1) {
      multipleWikindices = true;
      var params = document.getElementById("wikindx-finalize-order").value;
      split = params.split('_');
      order = split[0];
      ascDesc = split[1];
    }
    for (i = 0; i < urls.length; i++) {
      finalizeGetReferencesXML(context, urls[i], JSON.stringify(cleanIDs[urls[i]]));
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
      finalizeGetCitationsXML(context, urls[i], JSON.stringify(citeIDs[urls[i]]));
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
    finalizeGetBibliography();
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
    .then(function () {
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
function getCleanIDs(cc) {
  var i, item, metaId;
  var split = [];
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
}
function finalizeGetBibliography() {
  var i, key;
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
      });
    }
    else if (order == 'title') {
      multipleOrder.sort(function (a, b) {
        return a.title.localeCompare(b.title) || a.creator.localeCompare(b.creator) || a.year - b.year;
      });
    } else { // year
      multipleOrder.sort(function (a, b) {
        return a.year - b.year || a.creator.localeCompare(b.creator) || a.title.localeCompare(b.title);
      });
    }
    if (ascDesc == 'DESC') {
      multipleOrder.reverse();
    }
    for (i = 0; i < multipleOrder.length; i++) {
      key = multipleOrder[i].index;
      bibliography += '<p>' + finalizeReferences[key] + '</p>';
    }
  }
}
function finalizeGetCitationsXML(context, url, idString) {
  var tag, cc, j;
  Xml.finalizeGetCitations(url, idString);
  // Replace in-text references
  allItemObjects = []; // Reset . . .
  for (j = 0; j < Xml.xmlResponse.length; j++) {
    tag = 'wikindx-id-' + btoa(JSON.stringify([url, Xml.xmlResponse[j].id, Xml.xmlResponse[j].metaId]));
    cc = context.document.contentControls.getByTag(tag);
    cc.load('items');
    let items = {
      cc: cc,
      text: Xml.xmlResponse[j].inTextReference
    };
    allItemObjects.push(items);
  }
}

function finalizeGetReferencesXML(context, url, idString) {
  var key, tag, cc, j;

  Xml.finalizeGetReferences(url, idString);
  // Replace in-text references
  for (j = 0; j < Xml.xmlResponse.length; j++) {
    tag = 'wikindx-id-' + btoa(JSON.stringify([url, Xml.xmlResponse[j].id]));
    cc = context.document.contentControls.getByTag(tag);
    cc.load('items');
    let items = {
      cc: cc,
      text: Xml.xmlResponse[j].inTextReference
    };
    allItemObjects.push(items);
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
