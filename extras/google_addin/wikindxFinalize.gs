function finalizeDisplay() {
// Before displaying the pane, check we have references and remove any empty wikindx-based namedranges
  var found = false;
  var url, tag, text, split, i, j;
  var rangeElements = [];
  finalizeWikindices = [];

  var document = DocumentApp.getActiveDocument();
  var ranges = document.getNamedRanges();
  for (i = 0; i < ranges.length; i++) {
    rangeElements = ranges[i].getRange().getRangeElements();
    tag = ranges[i].getName();
    split = tag.split('W!K!NDX');
    if ((split.length < 3) || (split[1] != 'id')) {
      continue;
    }
    // If we get here, we have references
    for (j = 0; j < rangeElements.length; j++) {
      text = rangeElements[j].getElement().editAsText().getText();
      found = true;
      url = JSON.parse(split[2])[0];
      wikindicesPush(url);
    }
  }
  if (!found) { // Nothing stored yet for this document
    return {
      xmlResponse: false,
      message: "You have not inserted any references or citations yet so there is nothing to finalize."
    };
  }
  // If we get here, there is something to finalize. Check there are styles
  var styles = finalizeGetStyles();
  if (!styles) {
    if (finalizeXmlError) {
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
    styles: styles
  }
}
function finalizeRun(params, style) {
  var split, urls, response, i, j, k;
  var ranges = [];

  finalizeFoundBibliography = false;
  finalizeBibliography = '';
  finalizeReferences = [];
  finalizeMultipleOrder = [];
  finalizeCleanIDs = new Object();
  finalizeCiteIDs = new Object();
  var document = DocumentApp.getActiveDocument();
  var ranges = document.getNamedRanges();
  getCleanIDs(ranges);
  // get references from WIKINDX
  urls = Object.keys(finalizeCleanIDs);
  finalizeMultipleWikindices = false;
  if (urls.length > 1) {
    finalizeMultipleWikindices = true;
    split = params.split('_');
    finalizeOrder = split[0];
    finalizeAscDesc = split[1];
  }
  for (i = 0; i < urls.length; i++) {
    response = finalizeGetReferencesXML(urls[i], params, style, JSON.stringify(finalizeCleanIDs[urls[i]]), document);
    if (response.xmlResponse === false) {
      return {
        xmlResponse: false,
        message: response.message
      };
    }
  }
  for (i = 0; i < urls.length; i++) {
    response = finalizeGetCitationsXML(urls[i], style, JSON.stringify(finalizeCiteIDs[urls[i]]), document);
    if (response.xmlResponse === false) {
      return {
        xmlResponse: false,
        message: response.message
      };
    }
  }
  finalizeGetBibliography();
  if (finalizeFoundBibliography) {
    var document = DocumentApp.getActiveDocument();
    var ranges = document.getNamedRanges('wikindx-bibliography');
    ranges[0].remove();
    var rangeElements = ranges[0].getRange().getRangeElements();
// !!!! Even on a completely blank document that then has one reference inserted, google sometimes thinks it finds this tag . . .
// Check it has defined elements before using setText();
    if (rangeElements[0] != undefined) {
      rangeElements[0].getElement().asText().setText('');
    }
  }
  appendBibliography('\n\n\n\n' + finalizeBibliography);
  return {
    xmlResponse: true
  }
}
function getCleanIDs(ranges) {
  var i, item, metaId, id;
  var split = [];

  for (i = 0; i < ranges.length; i++) {
    rangeElements = ranges[i].getRange().getRangeElements();
    tag = ranges[i].getName();
    if (tag == 'wikindx-bibliography') {
      finalizeFoundBibliography = true;
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
      if (!(item[0] in finalizeCiteIDs)) {
        finalizeCiteIDs[item[0]] = [metaId];
      } else if (!finalizeCiteIDs[item[0]].includes(metaId)) {
        finalizeCiteIDs[item[0]].push(metaId);
      }
    }
    if (!(item[0] in finalizeCleanIDs)) {
      finalizeCleanIDs[item[0]] = [id];
    } else if (!finalizeCleanIDs[item[0]].includes(id)) {
      finalizeCleanIDs[item[0]].push(id);
    }
  }
}
function finalizeGetBibliography() {
  var i, key;
  if (!finalizeMultipleWikindices) {
    for (i = 0; i < finalizeReferences.length; i++) {
      let text = finalizeReferences[i];
      finalizeBibliography += text + '\n';
    }
  } else {
    if (finalizeOrder == 'creator') {
      finalizeMultipleOrder.sort(function (a, b) {
        return a.creator.localeCompare(b.creator) || a.year - b.year || a.title.localeCompare(b.title);
      });
    }
    else if (finalizeOrder == 'title') {
      finalizeMultipleOrder.sort(function (a, b) {
        return a.title.localeCompare(b.title) || a.creator.localeCompare(b.creator) || a.year - b.year;
      });
    } else { // year
      finalizeMultipleOrder.sort(function (a, b) {
        return a.year - b.year || a.creator.localeCompare(b.creator) || a.title.localeCompare(b.title);
      });
    }
    if (finalizeAscDesc == 'DESC') {
      finalizeMultipleOrder.reverse();
    }
    for (i = 0; i < finalizeMultipleOrder.length; i++) {
      key = finalizeMultipleOrder[i].index;
      finalizeBibliography += finalizeReferences[key] + '\n';
    }
  }
}
function finalizeGetCitationsXML(url, style, idString, document) {
  var tag, ranges, i;
  var response = finalizeGetCitations(url, style, idString);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  var jsonArray = response.xmlArray;
  for (i = 0; i < jsonArray.length; i++) {
    tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, jsonArray[i].id, jsonArray[i].metaId]);
    ranges = document.getNamedRanges(tag);
    updateRef(ranges, tag, jsonArray[i].inTextReference);
  }
  return {
    xmlResponse: true
  }
}
function finalizeGetReferencesXML(url, params, style, idString, document) {
  var tag, ranges, i;

  var response = finalizeGetReferences(url, params, style, idString);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  var jsonArray = response.xmlArray;
  for (i = 0; i < jsonArray.length; i++) {
    tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, jsonArray[i].id]);
    ranges = document.getNamedRanges(tag);
    updateRef(ranges, tag, jsonArray[i].inTextReference);
    finalizeReference(jsonArray[i].bibEntry, jsonArray[i].creatorOrder, jsonArray[i].titleOrder, jsonArray[i].yearOrder);
  }
  return {
    xmlResponse: true
  }
}
function updateRef(ranges, tag, inTextReference) {
  var j, element, rangeElements;

  for (j = 0; j < ranges.length; j++) {
    rangeElements = ranges[j].getRange().getRangeElements();
    element = rangeElements[0].getElement();
    ranges[j].remove();
    updateReference(element, tag, inTextReference);
  }
}
function finalizeReference(bibEntry, creatorOrder, titleOrder, yearOrder) {
  var key;

  if (!finalizeReferences.includes(bibEntry)) {
    finalizeReferences.push(bibEntry);
    if (finalizeMultipleWikindices) {
      key = finalizeReferences.indexOf(bibEntry);
      finalizeMultipleOrder.push({
        "index": key,
        "creator": creatorOrder,
        "title": titleOrder,
        "year": yearOrder
      });
    }
  }
}
function finalizeGetStyles() {
  var finalArray = [];
  var jsonArray = [];
  var tempLongNames = new Object();
  var styleLong, styleShort, i, j, len, url;
  var text = '';
  var stylesFound = false;

  for (i = 0; i < finalizeWikindices.length; i++) {
    var tempArray = [];
    url = finalizeWikindices[i][0];
    var response = getStyles(url);
    if (response.xmlResponse == null) {
      finalizeXmlError = true;
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
    finalizeWikindices.sort(function (a, b) { return b[1] - a[1]; });
    url = finalizeWikindices[0][0];
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
  var len = finalizeWikindices.length;
  for (var i = 0; i < len; i++) {
    if (url == finalizeWikindices[i][0]) {
        finalizeWikindices[i][1]++;
        return;
    }
  }
  finalizeWikindices.push([url, 1]);
}

