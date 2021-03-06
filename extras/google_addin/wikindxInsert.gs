var regexpItalics = RegExp(/(<em>)(.*?)(<\/em>)/, 'g');
var regexpBold = RegExp(/(<strong>)(.*?)(<\/strong>)/, 'g');
var regexpUnderline = RegExp(/(<span style="text-decoration: underline;">)(.*?)(<\/span>)/, 'g');
var regexpSub = RegExp(/(<sub>)(.*?)(<\/sub>)/, 'g');
var regexpSup = RegExp(/(<sup>)(.*?)(<\/sup>)/, 'g');
var regexpHref = RegExp(/(<a class="rLink" href=".*>)(.*?)(<\/a>)/, 'g');
var deleteArray = [];

function insertReference(url, style, id) {
  var i;
  var document = DocumentApp.getActiveDocument();
  var cursor = document.getCursor();
  var response = getReference(url, style, id);
  if (!response.xmlResponse) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // All good. Insert in-text reference into document, format it and return
  var element = cursor.insertText(response.xmlArray['inTextReference']);
  var styles = ['italics', 'bold', 'underline', 'super', 'sub', 'href'];
  for (i = 0; i < styles.length; i++) {
    insertHtmlText(element, response.xmlArray['inTextReference'], styles[i]);
  }
  deleteArray.sort(function (b, a) {
    return a.end - b.end;
  });
  for(i = 0; i < deleteArray.length; i++) {
    element.deleteText(deleteArray[i].start, deleteArray[i].end);
  }
  return {
    xmlResponse: true,
  };
}

function insertCitation(url, style, id) {
  var i;
  var document = DocumentApp.getActiveDocument();
  var cursor = document.getCursor();
  var response = getCitation(url, style, id);
  if (!response.xmlResponse) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // All good. Insert in-text reference into document, format it and return
  var element = cursor.insertText(' ' + response.xmlArray['inTextReference']);
  var styles = ['italics', 'bold', 'underline', 'super', 'sub', 'href'];
  for (i = 0; i < styles.length; i++) {
    insertHtmlText(element, response.xmlArray['inTextReference'], styles[i]);
  }
  deleteArray.sort(function (b, a) {
    return a.end - b.end;
  });
  for(i = 0; i < deleteArray.length; i++) {
    element.deleteText(deleteArray[i].start, deleteArray[i].end);
  }
  // Now add citation which has had HTML stripped at the WIKINDX end
  cursor.insertText(response.xmlArray['citation']);
  return {
    xmlResponse: true
  };
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
        element.setItalic(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'bold': 
      matches = [...text.matchAll(regexpBold)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.setBold(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'underline': 
      matches = [...text.matchAll(regexpUnderline)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.setUnderline(transformArray[i].textStart, transformArray[i].textEnd, true);
      }
      break;
    case 'super': 
      matches = [...text.matchAll(regexpSup)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.setTextAlignment(transformArray[i].textStart, transformArray[i].textEnd, DocumentApp.TextAlignment.SUPERSCRIPT);
      }
      break;
    case 'sub': 
      matches = [...text.matchAll(regexpSub)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.setTextAlignment(transformArray[i].textStart, transformArray[i].textEnd, DocumentApp.TextAlignment.SUBSCRIPT);
      }
      break;
    case 'href': 
      matches = [...text.matchAll(regexpHref)];
      transformArray = findTags(matches);
      for(i = 0; i < transformArray.length; i++) {
        element.setLinkUrl(transformArray[i].textStart, transformArray[i].textEnd, transformArray[i].capture);
      }
      break;
    default: 
      return {
        xmlResponse: false,
        message: 'missing style input'
      };
  }
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