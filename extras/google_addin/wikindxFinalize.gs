var order, ascDesc, id;
var foundBibliography;
var bibliography;
var finalizeReferences = [];
var allItemObjects = [];
var multipleOrder = [];
var wikindices = [];
var multipleWikindices;
var cleanIDs = new Object();
var citeIDs = new Object();
var xmlError = false;
var errorNoInserts = "You have not inserted any references or citations yet so there is nothing to finalize.";
var misc;
var debug = [];

function finalizeDisplay() {
// Before displaying the pane, check we have references and remove any empty wikindx-based namedranges
  var found = false;
  var url, tag, text, split, i, j;
  var rangeElements = [];
  wikindices = [];

  var document = DocumentApp.getActiveDocument();
  var ranges = document.getNamedRanges();
  for (i = 0; i < ranges.length; i++) {
    rangeElements = ranges[i].getRange().getRangeElements();
    tag = ranges[i].getName();
    split = tag.split('W!K!NDX');
    for (j = 0; j < rangeElements.length; j++) {
      text = rangeElements[j].getElement().getText();
      debug.push(["rangeNo. " + i + ': ' + "elementNo. " + j + ': ' + text]);
      if ((split.length > 1) && (split[0] === 'wikindx') && (text.trim() === '')) {
        return {xmlResponse: false, message: 'delete'};
        tag.delete();
        continue;
      }
      // 'looking for wikindxW!K!NDXidW!K!NDX{[JSON string/array]}'
      if ((split.length < 3) || (split[0] != 'wikindx')) {
        continue;
      }
      if (split[1] != 'id') {
        continue;
      }
      // If we get here, we have references
      found = true;
      url = JSON.parse(split[2])[0];
      wikindicesPush(url);
    }
  }
/*      return {
      xmlResponse: false,
      message: 'debugging',
      debug: debug
    };
*/  if (!found) { // Nothing stored yet for this document
    return {
      xmlResponse: false,
      message: errorNoInserts,
      debug: debug
    };
  }
  // If we get here, there is something to finalize. Check there are styles
  var styles = finalizeGetStyles();
  if (!styles) {
    if (xmlError) {
      return {
        xmlResponse: false,
        message: errorXMLHTTP
      };
    }
    return {
      xmlResponse: false,
      message: errorStylesFinalize
    };
  }
  return {
    xmlResponse: true,
    debug: debug,
    styles: styles
  }
}
function finalizeRun(params, style) {
  var split, urls, response, i, j, k;
  var ranges = [];

  foundBibliography = false;
  bibliography = '';
  finalizeReferences = [];
  allItemObjects = [];
  multipleOrder = [];
  cleanIDs = new Object();
  citeIDs = new Object();
  var document = DocumentApp.getActiveDocument();
  var ranges = document.getNamedRanges();
  getCleanIDs(ranges);
  // get references from WIKINDX
  urls = Object.keys(cleanIDs);
  multipleWikindices = false;
  if (urls.length > 1) {
    multipleWikindices = true;
    split = params.split('_');
    order = split[0];
    ascDesc = split[1];
  }
  for (i = 0; i < urls.length; i++) {
    response = finalizeGetReferencesXML(urls[i], params, style, JSON.stringify(cleanIDs[urls[i]]), ranges);
    if (response.xmlResponse === false) {
      return {
        xmlResponse: false,
        message: response.message
      };
    }
  }
/*
  // get citation references from WIKINDX
  urls = Object.keys(citeIDs);
  for (i = 0; i < urls.length; i++) {
    finalizeGetCitationsXML(urls[i], style, JSON.stringify(citeIDs[urls[i]]));
  }
  for (j = 0; j < allItemObjects.length; j++) {
    let itemObject = allItemObjects[j];
    for (k = 0; k < itemObject.cc.items.length; k++) {
      let item = itemObject.cc.items[k];
      let itemText = itemObject.text;
      item.insertHtml(itemText, 'Replace');
    }
  }
*/
  finalizeGetBibliography();
  if (foundBibliography) {
    var document = DocumentApp.getActiveDocument();
    var ranges = document.getNamedRanges();
    for (var i = 0; i < ranges.length; i++) {
      if (ranges[i].getName() == 'wikindx-bibliography') {
        var rangeElements = ranges[i].getRange().getRangeElements();
        rangeElements[0].getElement().asText().setText('');
        ranges[i].remove();
        break;
      }
    }
  }
  appendBibliography('\n\n\n\n' + bibliography);
  return {
    xmlResponse: true,
    debug: debug,
    misc: bibliography
  }
}
function getCleanIDs(ranges) {
  var i, item, metaId;
  var split = [];

  for (i = 0; i < ranges.length; i++) {
    rangeElements = ranges[i].getRange().getRangeElements();
    tag = ranges[i].getName();
    if (tag == 'wikindx-bibliography') {
      foundBibliography = true;
      continue;
    }
    split = tag.split('W!K!NDX');
    // 'looking for wikindxW!K!NDXidW!K!NDX{[JSON string/array]}'
    if ((split.length < 3) || (split[0] != 'wikindx')) {
      continue;
    }
    if (split[1] != 'id') {
      continue;
    }
    item = JSON.parse(split[2]);
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
      bibliography += finalizeReferences[i] + '\n';
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
      bibliography += finalizeReferences[key] + '\n';
    }
  }
}
function finalizeGetCitationsXML(url, style, idString) {
  var tag, cc, j;
  Xml.finalizeGetCitations(url, idString);
  // Replace in-text references
  allItemObjects = []; // Reset . . .
  for (j = 0; j < Xml.xmlResponse.length; j++) {
    tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, Xml.xmlResponse[j].id, Xml.xmlResponse[j].metaId]);
    cc = context.document.contentControls.getByTag(tag);
    cc.load('items');
    let items = {
      cc: cc,
      text: Xml.xmlResponse[j].inTextReference
    };
    allItemObjects.push(items);
  }
}
function finalizeGetReferencesXML(url, params, style, idString, ranges) {
  var key, tag, rangeElements, element, i, j, k;

  var response = finalizeGetReferences(url, params, style, idString);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // Replace in-text references
  var jsonArray = response.xmlArray;
  for (i = 0; i < jsonArray.length; i++) {
    tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, jsonArray[i].id]);
    let items = {
      tag: tag,
      text: jsonArray[i].inTextReference
    };
    for (j = 0; j < ranges.length; j++) {
      if (tag == ranges[j].getName()) {
        rangeElements = ranges[j].getRange().getRangeElements();
        for (k = 0; k < rangeElements.length; k++) {
          element = rangeElements[k].getElement();
          debug.push([element.asText().getText(), jsonArray[i].inTextReference]);
        }
        updateReference(element, tag, jsonArray[i].inTextReference);
//    allItemObjects.push(items);
        if (!finalizeReferences.includes(jsonArray[i].bibEntry)) {
          finalizeReferences.push(jsonArray[i].bibEntry);
          if (multipleWikindices) {
            key = finalizeReferences.indexOf(jsonArray[i].bibEntry);
            multipleOrder.push({
              "index": key,
              "creator": jsonArray[i].creatorOrder,
              "title": jsonArray[i].titleOrder,
              "year": jsonArray[i].yearOrder
            });
          }
        }
      }
    }
  }misc = finalizeReferences;
  return {
    xmlResponse: true
  }
}

function finalizeGetStyles() {
  var finalArray = [];
  var jsonArray = [];
  var tempLongNames = new Object();
  var styleLong, styleShort, i, j, len, url;
  var text = '';
  var stylesFound = false;

  for (i = 0; i < wikindices.length; i++) {
    var tempArray = [];
    url = wikindices[i][0];
    var response = getStyles(url);
    if (response.xmlResponse == null) {
      xmlError = true;
      return false;
    }
    stylesFound = true;
    // first run through – gather all styles from first WIKINDX
    jsonArray = response.xmlArray;
    if (!i) {
      for (j = 0; j < jsonArray.length; j++) {
        finalArray.push(jsonArray[j].styleShort);
        tempLongNames[jsonArray[j].styleShort] = jsonArray[j].styleLong;
      }
      continue;
    } // else . . .
    for (j = 0; j < jsonArray.length; j++) {
      tempArray.push(jsonArray[j].styleShort);
      tempLongNames[jsonArray[j].styleShort] = jsonArray[j].styleLong;
    }
    finalArray = finalArray.filter(value => tempArray.includes(value));
  }
  if (!stylesFound) {
    return false;
  }
  if (!finalArray.length) {
  // Get styles from wikindx with most intext references
    wikindices.sort(function (a, b) { return b[1] - a[1]; });
    url = wikindices[0][0];
    var response = getStyles(url);
    finalArray = [];
    tempLongNames = [];
    jsonArray = response.xmlArray;
    for (j = 0; j < jsonArray.length; j++) {
      finalArray.push(jsonArray[j].styleShort);
      tempLongNames[jsonArray[j].styleShort] = jsonArray[j].styleLong;
    }
  }
  for (i = 0; i < finalArray.length; i++) {
    styleShort = finalArray[i];
    styleLong = tempLongNames[finalArray[i]];
    text += '<option value="' + styleShort + '">' + styleLong + '</option>';
  }
  return text;
}

function wikindicesPush(url) {
  var len = wikindices.length;
  for (var i = 0; i < len; i++) {
    if (url == wikindices[i][0]) {
        wikindices[i][1]++;
        return;
    }
  }
  wikindices.push([url, 1]);
}
