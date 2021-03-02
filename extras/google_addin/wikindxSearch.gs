var initialId = false;
var bibEntry = '';
var inTextReference = '';
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
  var cursor = DocumentApp.getActiveDocument().getCursor();
  //var regex = /\<em\>(.*?)\<\/em>/g;
  //var newStr = response.bibEntry.replace(regex, cursor.setItalic($1));
/*
var pos = 0;
var num = -1;
var i = -1;
var text = "<em>it</em> no it <em>it</em>";
var answer = '';
  while (pos != -1) {
    pos = text.indexOf("<em>", i + 1);
//    i = pos;
    answer += num + " <em> found position " + pos + ' - ';
    pos = text.indexOf("</em>", i + 1);
    i = pos;
    answer += num + " </em> found position " + pos + ' - ';
    num += 1;
  }
*/
// expect 
let text = "<em>it1</em> no it <em>it2</em>";
//let regexp = RegExp("(<em>)(.*?)(<\/em>)", 'g');
let matchArray = [...text.matchAll(regexp)];
const regexp = RegExp('foo[a-z]*','g');
const str = 'table football, foosball';
const matches = str.matchAll(regexp);
      return {
      xmlResponse: false,
      message: response.message,
      matchArray: matches
    };

//cursor.insertText(response.bibEntry);
 cursor.insertText(answer).setItalic(3, 5, true).setUnderline(0, 5, true).setItalic(8, 12, true);
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
  cursor.insertText('here: ' + response.citation);
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
