function finalizeGetReferences(url, params, style, ids) {
  url += "office.php";
  var formData = {
    method: 'getBib',
    searchParams: params,
    style: style,
    source: 'googleDocs',
    ids: ids
  };
  return doXml(url, formData);
}
function finalizeGetCitations(url, style, ids) {
  url += "office.php";
  var formData = {
    method: 'getCiteCCs',
    style: style,
    source: 'googleDocs',
    ids: ids
  };
}

function getSearchInputReferences(url, params, style, searchText) {
  url += "office.php";
  var formData = {
    method: 'getReferences',
    searchWord: searchText,
    searchParams: params,
    style: style,
    source: 'googleDocs'
  };
  return doXml(url, formData);
}
function getReference(url, style, id) {
  url += "office.php";
  var formData = {
    method: 'getReference',
    style: style,
    id: id,
    source: 'googleDocs'
  };
  return doXml(url, formData);
}
function getSearchInputCitations(url, params, style, searchText) {
  url += "office.php";
  var formData = {
    method: 'getCitations',
    searchWord: searchText,
    searchParams: params,
    style: style,
    source: 'googleDocs'
  };
  return doXml(url, formData);
}
function getCitation(url, style, id) {
  url += "office.php";
  var formData = {
    method: 'getCitation',
    style: style,
    id: id,
    withHtml: '0',
    source: 'googleDocs'
  };
  return doXml(url, formData);
}
function heartbeat(url) {
  url += "office.php";
  var formData = {
    method: 'heartbeat',
    source: 'googleDocs'
  };
return {
  xmlResponse: true,
  message: successHeartbeat
};
//  return doXml(url, formData);
}
function userCheckHeartbeat(url) {
  response = heartbeat(url);
    if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  } else {
    return {
      xmlResponse: true,
      message: successHeartbeat
    };
  }
}
function getStyles(url) {
  url += "office.php";
  var formData = {
    method: 'getStyles',
    source: 'googleDocs'
  };
  return doXml(url, formData);
}
function doXml(url, formData) {
  var options = {
    method: 'post',
    payload: formData,
    muteHttpExceptions: true
  };
  try {
    var response = UrlFetchApp.fetch(url, options);
//    var response = UrlFetchApp.getRequest(url, options);
    if (response === null) {
      return {
        xmlResponse: false,
        message: errorXMLHTTP
      };
    } else if (response.getResponseCode() != 200) {
      return {
        xmlResponse: false,
        message: errorXMLHTTP
      };
    } else if(JSON.parse(response.getContentText()) == 'access denied'){
      return {
        xmlResponse: false,
        message: errorAccess
      };
    } else {
      return {
        xmlResponse: true,
        xmlArray: JSON.parse(response.getContentText())
      };
    }
  } catch(e) {
      return {
        xmlResponse: false,
        url: url + '?' + response.payload,
        message: errorXMLHTTP
      };
  }
}