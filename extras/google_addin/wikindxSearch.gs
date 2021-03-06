var initialId = false;
var bibEntry = '';
var citation = '';

function searchReferences(url, params, style, searchText) {
  var response = getSearchInputReferences(url, params, style, searchText);
  if (response.xmlResponse === false) {
      return {
      xmlResponse: false,
      message: errorNoResultsReferences
    };
  }
  var refSelectBox = printSearchResultsReferences(response.xmlArray);
  response = displayReference(url, style, initialId);
  if (response.xmlResponse === false) {
      return {
      xmlResponse: false,
      message: response.message
    };
  }
  return {
    xmlResponse: true,
    refSelectBox: refSelectBox,
    bibEntry: response.bibEntry
  };
}

function printSearchResultsReferences(xmlArray) {
  var refSelectBox = '';

  for (var i = 0; i < xmlArray.length; i++) {
    bibEntry = xmlArray[i].bibEntry;
    id = xmlArray[i].id;
    refSelectBox += '<option value="' + id + '">' + bibEntry + '</option>';
    if (i == 0) {
      initialId = id;
    }
  }
  return refSelectBox;
}

function displayReference(url, style, id) {
  var response = getReference(url, style, id);
  if (response.xmlResponse == 'Bad ID') {
    return {
      xmlResponse: false,
      message: errorMissingID
    };
  }
  return {
    xmlResponse: true,
    bibEntry: response.xmlArray['bibEntry']
  };
}

function searchCitations(url, params, style, searchText) {
  var response = getSearchInputCitations(url, params, style, searchText);
  if (response.xmlResponse === false) {
      return {
      xmlResponse: false,
      message: errorNoResultsReferences
    };
  }
  var citeSelectBox = printSearchResultsCitations(response.xmlArray);
  response = displayCitation(url, style, initialId);
  var cursor = DocumentApp.getActiveDocument().getCursor();
//  cursor.insertText('here: ' + response.citation);
  if (response.xmlResponse === false) {
      return {
      xmlResponse: false,
      message: response.message
    };
  }
  return {
    xmlResponse: true,
    citeSelectBox: citeSelectBox,
    citation: response.citation,
    bibEntry: response.bibEntry
  };
}

function printSearchResultsCitations(xmlArray) {
  var citeSelectBox = '';

  for (var i = 0; i < xmlArray.length; i++) {
    citation = xmlArray[i].citation;
    id = xmlArray[i].id;
    citeSelectBox += '<option value="' + id + '">' + citation + '</option>';
    if (i == 0) {
      initialId = id;
    }
  }
  return citeSelectBox;
}

function displayCitation(url, style, id) {
  var response = getCitation(url, style, id);
  if (response.xmlResponse == 'Bad ID') {
    return {
      xmlResponse: false,
      message: errorMissingID
    };
  }
  return {
    xmlResponse: true,
    citation: response.xmlArray['citation'],
    bibEntry: response.xmlArray['bibEntry']
  };
}
