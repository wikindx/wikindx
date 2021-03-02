var selectedURL = '';

function urlDeleteDisplay() {
  var prefs = getLocalStorage();
  var jsonArray = JSON.parse(prefs.localStorage);
  var id;
  var text = '';
  var hidden = [];

  for (var i = 0; i < jsonArray.length; i++) {
    id = jsonArray[i][0] + '--WIKINDX--' + jsonArray[i][1];
    hidden.push(id);
    text += '<input type="checkbox" id="' + id + '" name="' + id + '">'
      + '<label for="' + id + '"> ' + jsonArray[i][1] + ': ' + jsonArray[i][0] + '</label><br/>';
  }
  text += '<button class="button" id="wikindx-url-remove" alt="Delete URLs" title="Delete URLs">Delete&nbsp;URLs</button>';
  text += '<button class="button" id="wikindx-close-url-remove" alt="Close" title="Close">Close</button>';
  return {
    text: text,
    hidden: hidden
  };
}
function urlDelete(keep) {
  var prefs = getLocalStorage();
  var jsonArray = JSON.parse(prefs.localStorage);

  if (keep.length == jsonArray.length) {
    return { // Nothing to do . . .
      done: false
    };
  }
  if (!keep.length) { // Have we completely emptied the list?
    var userProperties = PropertiesService.getUserProperties();
    userProperties.deleteAllProperties();
    return {
      done: true,
      xmlResponse: true, // faking it . . .
      numStoredURLs: 0,
      message: successRemoveAllUrls
    };
  }
  selectedURL = keep[0][0];
  response = styleSelectBox(selectedURL);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  setLocalStorage(keep);
  var urlSelectBox = getUrlSelectBox(keep);
  return {
    done: true,
    xmlResponse: true, // faking it . . .
    numStoredURLs: keep.length,
    message: successRemoveUrl,
    urlSelectBox: urlSelectBox,
    selectedURL: selectedURL,
    styleSelectBox: response.styleSelectBox
  };
}
function urlPreferredDisplay() {
  var prefs = getLocalStorage();
  var jsonArray = JSON.parse(prefs.localStorage);
  var id;
  var text = '';
  var checked = 'checked';
  for (var i = 0; i < jsonArray.length; i++) {
    if (i) {
      checked = '';
    }
    id = jsonArray[i][1]; // The WIKINDX name
    text += '<input type="radio" id="' + id + '" name="wikindx-preferred" value="' + id + '"' + checked + '>'
      + '<label for="' + id + '"> ' + jsonArray[i][1] + ': ' + jsonArray[i][0] + '</label><br/>';
  }
  text += '<button class="button" id="wikindx-url-prefer" alt="Set preferred WIKINDX" title="Set preferred WIKINDX">Store</button>';
  text += '<button class="button" id="wikindx-close-url-preferred" alt="Close" title="Close">Close</button>';
  return {
    text: text
  };
}
function urlPrefer(name) {
  // What is in position [0] of the array is the preferrred URL . . .
  var prefs = getLocalStorage();
  var jsonArray = JSON.parse(prefs.localStorage);
  var newArray = [];
  var zeroth = [];

  if (name != jsonArray[0][1]) { // Selected value not yet in position 0
    zeroth = jsonArray[0];
    for (var i = 1; i < jsonArray.length; i++) {
      if (name == jsonArray[i][1]) {
        newArray[0] = jsonArray[i];
      } else {
        newArray[i] = jsonArray[i];
      }
    }
    newArray.push(zeroth);
    jsonArray = newArray;
  }
  selectedURL = jsonArray[0][0];
  response = styleSelectBox(selectedURL);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  setLocalStorage(jsonArray);
  var urlSelectBox = getUrlSelectBox(jsonArray);
  return {
    xmlResponse: true,
    message: successPreferredUrl,
    selectedURL: selectedURL,
    urlSelectBox: urlSelectBox,
    styleSelectBox: response.styleSelectBox
  };
}
function urlRegister(url, name) {
  var response = heartbeat(url);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // All OK . . .
  var prefs = getLocalStorage();
  if (prefs.localStorage === null) {
    var jsonArray = [[url, name]];
  } else {
    var jsonArray = JSON.parse(prefs.localStorage);
    for (var i = 0; i < jsonArray.length; i++) {
      if ((url == jsonArray[i][0])) {
        return {
          xmlResponse: false,
          message: errorDuplicateUrl
        };
      } else if (name == jsonArray[i][1]) {
        return {
          xmlResponse: false,
          message: errorDuplicateName
        };
      }
    }
    jsonArray.push([url, name]);
  }
  // Get styles for new WIKINDX
  response = styleSelectBox(url);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  setLocalStorage(jsonArray);
  selectedURL = url;
  var urlSelectBox = getUrlSelectBox(jsonArray);
  return {
    xmlResponse: true,
    numStoredURLs: jsonArray.length,
    message: successNewUrl + name + ' (' + url + ')',
    selectedURL: url,
    urlSelectBox: urlSelectBox,
    styleSelectBox: response.styleSelectBox
  };
}
function getUrlSelectBox(jsonArray) {
  var urlSelectBox = '';

  if (!selectedURL) {
    selectedURL = jsonArray[0][0];
  }
  for (var i = 0; i < jsonArray.length; i++) {
    if (selectedURL == jsonArray[i][0]) {
      urlSelectBox += '<option value="' + jsonArray[i][0] + '" selected>' + jsonArray[i][1] + '</option>';
    } else {
      urlSelectBox += '<option value="' + jsonArray[i][0] + '">' + jsonArray[i][1] + '</option>';
    }
  }
  return urlSelectBox;
}
function urlEditDisplay(url) {
  var prefs = getLocalStorage();
  var jsonArray = JSON.parse(prefs.localStorage);
  for (var i = 0; i < jsonArray.length; i++) {
    if (url == jsonArray[i][0]) {
      break;
    }
  }
  return {
    selectedURL: url,
    selectedName: jsonArray[i][1]
  };
}
function urlEdit(url, name, editURL) {
  var response = heartbeat(url);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // All OK . . .
  var prefs = getLocalStorage();
  var jsonArray = JSON.parse(prefs.localStorage);
  // get index of original
  for (var i = 0; i < jsonArray.length; i++) {
    if (editURL == jsonArray[i][0]) {
      var selected_i = i;
      break;
    }
  }
  for (var i = 0; i < jsonArray.length; i++) {
    if (i == selected_i) {
      continue;
    }
    if ((url == jsonArray[i][0])) {
      return {
        xmlResponse: false,
        message: errorDuplicateUrl
      };
    } else if (name == jsonArray[i][1]) {
      return {
        xmlResponse: false,
        message: errorDuplicateName
      };
    }
  }
  // Get styles for edited WIKINDX (URL might have changed)
  response = styleSelectBox(url);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  // If we get here, we're cleared to stored the edits
  jsonArray[selected_i][0] = url;
  jsonArray[selected_i][1] = name;
  setLocalStorage(jsonArray);
  selectedURL = url;
  var urlSelectBox = getUrlSelectBox(jsonArray);
  return {
    xmlResponse: true,
    message: successEditUrl,
    selectedURL: selectedURL,
    urlSelectBox: urlSelectBox,
    styleSelectBox: response.styleSelectBox
  };
}