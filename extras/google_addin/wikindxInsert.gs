var regexpItalics = RegExp(/(<em>)(.*?)(<\/em>)/, 'g');
var regexpBold = RegExp(/(<strong>)(.*?)(<\/strong>)/, 'g');
var regexpUnderline = RegExp(/(<span style="text-decoration: underline;">)(.*?)(<\/span>)/, 'g');
var regexpSub = RegExp(/(<sub>)(.*?)(<\/sub>)/, 'g');
var regexpSup = RegExp(/(<sup>)(.*?)(<\/sup>)/, 'g');
var regexpHref = RegExp(/(<a class="rLink" href=".*>)(.*?)(<\/a>)/, 'g');
var styles = ['italics', 'bold', 'underline', 'super', 'sub', 'href'];
var deleteArray = [];
var transformArray = [];
var updatePosition = 0;
var insertError = "ERROR: Invalid cursor position. A reference cannot replace a selection so position the cursor where you wish to insert the reference.";
var blankImage = UrlFetchApp.fetch('https://www.wikindx.com/wikindx-addins/blank.png');
var blob = blankImage.getBlob();

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
  for (i = 0; i < styles.length; i++) {
    insertHtmlText(element, response.xmlArray['inTextReference'], styles[i]);
  }
  deleteTags(element);
  var rangeBuilder = document.newRange();
  rangeBuilder.addElement(element);
  var tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, id]);
  document.addNamedRange(tag, rangeBuilder.build());
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
  var element = newElement(document, response.xmlArray['inTextReference']);
  if (!element) {
    return {
      xmlResponse: false,
      message: insertError
    };
  }
  for (i = 0; i < styles.length; i++) {
    insertHtmlText(element, text, styles[i]);
  }
  deleteTags(element);
  var rangeBuilder = document.newRange();
  rangeBuilder.addElement(element);
  var tag = 'wikindxW!K!NDXidW!K!NDX' + JSON.stringify([url, id, response.xmlArray['metaId']]);
  document.addNamedRange(tag, rangeBuilder.build());
// Now insert the actual citation
  var cursor = document.getCursor();
  cursor.insertText(response.xmlArray['citation']);
  return {
    xmlResponse: true
  };
}
function updateReference(element, tag, replacementText) {
  var document, rangeBuilder;
  deleteArray = []; // reset

  element.setText(replacementText);
//  updatePosition = element.findText(replacementText).getStartOffset();
  for (var i = 0; i < styles.length; i++) {
    insertHtmlText(element, replacementText, styles[i]);
  }
  deleteTags(element);
  var document = DocumentApp.getActiveDocument();
  var rangeBuilder = document.newRange();
  rangeBuilder.addElement(element);
  document.addNamedRange(tag, rangeBuilder.build());
  for(i = 0; i < deleteArray.length; i++) {
    debug.push([element.editAsText().getText(), deleteArray[i].start, deleteArray[i].end])
  }
  return debug;
}
function appendBibliography(text) {
  updatePosition = 0; // reset
  transformArray = []; // reset
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
/*
// First, check if something is selected (i.e. we replace)
  var selection = document.getSelection();
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
  }
  */
  var cursor = document.getCursor();
  if (cursor) {
    cursor.insertInlineImage(blob);
    var element = cursor.insertText(ref);
    cursor.insertInlineImage(blob);
  } else {
    element = null;
  }
  return element;
}
function doRegExp(text, regexp) {
  var matches = [];

  matches = [...text.matchAll(regexp)];
  if (!matches.length) {
    return;
  }
  return findTags(matches);
}
function insertHtmlText(element, text, style) {
  var i;
  transformArray = []; // reset

  switch (style) {
    case 'italics': 
      doRegExp(text, regexpItalics);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setItalic(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'bold': 
      doRegExp(text, regexpBold);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setBold(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'underline': 
      doRegExp(text, regexpUnderline);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setUnderline(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'super': 
      doRegExp(text, regexpSup);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setTextAlignment(transformArray[i].textStart, transformArray[i].textEnd, DocumentApp.TextAlignment.SUPERSCRIPT);
      }
      break;
    case 'sub': 
      doRegExp(text, regexpSub);
      for(i = 0; i < transformArray.length; i++) {
        element.editAsText().setTextAlignment(transformArray[i].textStart, transformArray[i].textEnd, DocumentApp.TextAlignment.SUBSCRIPT);
      }
      break;
    case 'href': 
      doRegExp(text, regexpHref);
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

  for (let match of matches) {
    openTagStart = match.index + updatePosition;
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
}
function deleteTags(element) {
  if (!deleteArray.length) {
    return;
  }
  deleteArray.sort(function (b, a) {return a.end - b.end;});
  for(i = 0; i < deleteArray.length; i++) {
    element.editAsText().deleteText(deleteArray[i].start, deleteArray[i].end);
  }
}
