function getBlankBlob() {
  var url = 'https://www.wikindx.com/wikindx-addins/blank.png';
  var formData = {};
  return doXmlBlob(url, formData);
}

function getCitationCreators(url) {
  var formData = {
    method: 'getCreators'
  };
  return doXml(url, formData);
}

function finalizeGetReferences(url, params, style, ids) {
  var formData = {
    method: 'getBib',
    searchParams: params,
    style: style,
    ids: ids
  };
  return doXml(url, formData);
}
function finalizeGetCitations(url, style, ids) {
  var formData = {
    method: 'getCiteCCs',
    style: style,
    ids: ids
  };
  return doXml(url, formData);
}

function getSearchInputReferences(url, params, style, searchText) {
  var formData = {
    method: 'getReferences',
    searchWord: searchText,
    searchParams: params,
    style: style
  };
  return doXml(url, formData);
}
function getReference(url, style, id) {
  var formData = {
    method: 'getReference',
    style: style,
    id: id
  };
  return doXml(url, formData);
}
function getSearchInputCitations(url, params, style, searchText, andOr, creator) {
  var formData = {
    method: 'getCitations',
    searchWord: searchText,
    searchAndOr: andOr,
    searchCreator: creator,
    searchParams: params,
    style: style
  };
  return doXml(url, formData);
}
function getCitation(url, style, id) {
  var formData = {
    method: 'getCitation',
    style: style,
    id: id,
    withHtml: '0'
  };
  return doXml(url, formData);
}
function heartbeat(url) {
  var formData = {
    method: 'heartbeat'
  };
  return doXml(url, formData);
}
function userCheckHeartbeat(url) {
  var formData = {
    method: 'heartbeat'
  };
  var response = doXml(url, formData);
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
  var formData = {
    method: 'getStyles'
  };
  return doXml(url, formData);
}
function doXmlBlob(url, formData) {
  var options = {
    method: 'post',
    payload: formData,
    muteHttpExceptions: true
  };
  try {
    var response = UrlFetchApp.fetch(url, options);
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
    } else {
      return {
        xmlResponse: true,
        blob: response.getBlob()
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
function doXml(url, formData) {
  url += "office.php";
  formData.source = 'googleDocs';
  formData.compatibility = compatibility; // Set in wikindx.gs
  var options = {
    method: 'post',
    payload: formData,
    muteHttpExceptions: true
  };
  try {
    var response = UrlFetchApp.fetch(url, options);
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
    } else if(JSON.parse(response.getContentText()) == 'access denied') {
      return {
        xmlResponse: false,
        message: errorAccess
      };
    } else if(JSON.parse(response.getContentText()) == 'incompatible') {
      return {
        xmlResponse: false,
        message: errorCompatibility
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