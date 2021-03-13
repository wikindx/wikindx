var regexpItalics = RegExp(/(<em>)(.*?)(<\/em>)/, 'g');
var regexpBold = RegExp(/(<strong>)(.*?)(<\/strong>)/, 'g');
var regexpUnderline = RegExp(/(<span style="text-decoration: underline;">)(.*?)(<\/span>)/, 'g');
var regexpSub = RegExp(/(<sub>)(.*?)(<\/sub>)/, 'g');
var regexpSup = RegExp(/(<sup>)(.*?)(<\/sup>)/, 'g');
var regexpHref = RegExp(/(<a class="rLink" href=".*>)(.*?)(<\/a>)/, 'g');
var styles = ['italics', 'bold', 'underline', 'super', 'sub', 'href'];
var deleteArray = [];
var insertError = "ERROR: Invalid selection or cursor position";

function insertReference(url, style, id) {
  var i;
  var response = getReference(url, style, id);
  if (!response.xmlResponse) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // All good. Insert in-text reference into a named range at the cursor, format it and return
  var document = DocumentApp.getActiveDocument();
  var element = newElement(document, response.xmlArray['inTextReference']);
  if (!element) {
    return {
      xmlResponse: false,
      message: insertError
    };
  }
  var rangeBuilder = document.newRange();
  rangeBuilder.addElement(element);
  var tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, id]);
  document.addNamedRange(tag, rangeBuilder.build());
/*  var ranges = document.getNamedRanges();
  var rangeElements = [];
  var rangeTexts = [];
  var rangeNames = [];
  for (var i = 0; i < ranges.length; i++) {
    rangeElements = ranges[i].getRange().getRangeElements();
    rangeNames.push(ranges[i].getName());
    for (var j = 0; j < rangeElements.length; j++) {
      rangeTexts.push(rangeElements[j].getElement().getText());
    }
  }
*/
  for (i = 0; i < styles.length; i++) {
    insertHtmlText(element, response.xmlArray['inTextReference'], styles[i]);
  }
  deleteTags(element);
  return {
    xmlResponse: true
  };
}

function insertCitation(url, style, id) {
  var i;
  var response = getCitation(url, style, id);
  if (!response.xmlResponse) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // All good. Insert in-text reference into document, format it and return
  var document = DocumentApp.getActiveDocument();
  var text = response.xmlArray['citation'] + ' ' + response.xmlArray['inTextReference'];
  var element = newElement(document, text);
  if (!element) {
    return {
      xmlResponse: false,
      message: insertError
    };
  }
  var rangeBuilder = document.newRange();
  rangeBuilder.addElement(element);
  var tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, id, response.xmlArray['metaId']]);
  document.addNamedRange(tag, rangeBuilder.build());
  for (i = 0; i < styles.length; i++) {
    insertHtmlText(element, text, styles[i]);
  }
  deleteTags(element);
  return {
    xmlResponse: true
  };
}
function updateReference(element, tag, text) {
    element = element.setText(text);
    var document = DocumentApp.getActiveDocument();
    var rangeBuilder = document.newRange();
    rangeBuilder.addElement(element);
    document.addNamedRange(tag, rangeBuilder.build());
    for (var i = 0; i < styles.length; i++) {
      insertHtmlText(element, text, styles[i]);
  }
  deleteTags(element);
}
function appendBibliography(text) {
    var document = DocumentApp.getActiveDocument();
    var body = document.getBody();
    var element = body.appendParagraph(text);
    var rangeBuilder = document.newRange();
    rangeBuilder.addElement(element);
    document.addNamedRange('wikindx-bibliography', rangeBuilder.build());
    for (var i = 0; i < styles.length; i++) {
      insertHtmlText(element, text, styles[i]);
  }
  deleteTags(element);
}
function newElement(document, ref) {
// First, check if something is selected (i.e. we replace)
  var selection = document.getSelection();
  var cursor = document.getCursor();
  if (selection) {
    var rangeElements = selection.getRangeElements();
    var j = 0;
    for (j; j < rangeElements.length - 1; j++) {
      if (!rangeElements[j].isPartial()) { // Not satisfactory but will do for now. We ignore partial elements in the selection . . .
        rangeElements[j].getElement().asText().setText('');
      } else {
        rangeElements[j].getElement().asText().deleteText(rangeElements[j].getStartOffset(), rangeElements[j].getEndOffsetInclusive());
      }
    }
    if (rangeElements[j].isPartial()) {
      var selText = rangeElements[j].getElement().asText().getText();
      var element = rangeElements[j].getElement().asText().setText(selText + ' ' + ref);
    } else {
        var element = rangeElements[j].getElement().asText().setText(ref);
      }
  } else if (cursor) {
    var element = cursor.insertText(ref);
  } else {
    element = null;
  }
  return element;
}
function insertHtmlText(element, text, style) {
  var i;
  var matches = [];
  var transformArray = [];

  switch (style) {
    case 'italics': 
      matches = [...text.matchAll(regexpItalics)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setItalic(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'bold': 
      matches = [...text.matchAll(regexpBold)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setBold(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'underline': 
      matches = [...text.matchAll(regexpUnderline)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setUnderline(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'super': 
      matches = [...text.matchAll(regexpSup)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setTextAlignment(transformArray[i].textStart, transformArray[i].textEnd, DocumentApp.TextAlignment.SUPERSCRIPT);
      }
      break;
    case 'sub': 
      matches = [...text.matchAll(regexpSub)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setTextAlignment(transformArray[i].textStart, transformArray[i].textEnd, DocumentApp.TextAlignment.SUBSCRIPT);
      }
      break;
    case 'href': 
      matches = [...text.matchAll(regexpHref)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setLinkUrl(transformArray[i].textStart, transformArray[i].textEnd, transformArray[i].capture);
      }
      break;
    default: 
      return {
        xmlResponse: false,
        message: 'Missing style input'
      };
  }
  return {
    xmlResponse: true
  };
}
function findTags(matches) {
  var openTagStart, openTagEnd, textStart, textEnd, closeTagStart, closeTagEnd;
  var transformArray = [];

  for (let match of matches) {
    openTagStart = match.index;
    openTagEnd = openTagStart + match[1].length - 1;
    textStart = openTagEnd + 1;
    textEnd = textStart + match[2].length - 1;
    closeTagStart = textEnd + 1;
    closeTagEnd = closeTagStart + match[3].length -1;
    transformArray.push({
      openTagStart: openTagStart,
      openTagEnd: openTagEnd,
      textStart: textStart,
      textEnd: textEnd,
      closeTagStart: closeTagStart,
      closeTagEnd: closeTagEnd,
      capture: match[2]
    });
    deleteArray.push({start: openTagStart, end: openTagEnd});
    deleteArray.push({start: closeTagStart, end: closeTagEnd});
  }
  return transformArray;
}
function deleteTags(element) {
  deleteArray.sort(function (b, a) {
    return a.end - b.end;
  });
  for(i = 0; i < deleteArray.length; i++) {
    element.editAsText().deleteText(deleteArray[i].start, deleteArray[i].end);
  }
}
