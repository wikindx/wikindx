import { displayError, displaySuccess } from "./wikindxMessages";
import * as Visible from "./wikindxVisible";
import * as Xml from "./wikindxXml";
import * as LocalStorage from "./wikindxLocalStorage";
import * as Styles from "./wikindxStyles";

var errorNewUrl = "ERROR: Missing URL or name input.";
var successEditUrl = "Edited WIKINDX URL.";
var errorDuplicateUrl = "ERROR: Duplicate URL input.";
var errorDuplicateName = "ERROR: Duplicate name input.";
var successNewUrl = "Stored new WIKINDX: ";
var successRemoveUrl = "Deleted WIKINDX URL(s).";
var successRemoveAllUrls = "Deleted all WIKINDX URLs.";
var successPreferredUrl = "Preference stored.";

export function urlManagementDisplay(turnOn) {
  var displays = ["wikindx-url-entry", "wikindx-url-edit", "wikindx-urls-remove", "wikindx-urls-preferred"];
  for (var i = 0; i < displays.length; i++) {
    if (displays[i] != turnOn) {
      document.getElementById(displays[i]).style.display = "none";
    }
  }

  Visible.hideVisible(["wikindx-search-parameters", "wikindx-display-results"]);
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById(turnOn).style.display = "block";
  document.getElementById("wikindx-url-management").style.display = "block";
}
export function urlEditDisplay() {
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Edit WIKINDX';
  document.getElementById("wikindx-edit-url").value = Xml.selectedURL;
  document.getElementById("wikindx-edit-name").value = Xml.selectedName;
  urlManagementDisplay("wikindx-url-edit");
}
export function urlEdit() {
  var editUrl = document.getElementById("wikindx-edit-url");
  var url = editUrl.value.trim();
  var editName = document.getElementById("wikindx-edit-name");
  var name = editName.value.trim();
  if (!url) {
    displayError(errorNewUrl);
    return;
  }
  if (!name) {
    displayError(errorNewUrl);
    return;
  }
  // Add trailing '/'
  if (url.slice(-1) != '/') {
    url += '/';
  }
  if (url == Xml.selectedURL && name == Xml.selectedName) { // No change
    Visible.visibleElements.push("wikindx-search-parameters");
    Visible.retrieveVisible();
    document.getElementById("wikindx-url-management").style.display = "none";
    displaySuccess(successEditUrl);
    return;
  }
  var hrReturn = Xml.heartbeat(url);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }

  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  // get index of original
  for (var i = 0; i < jsonArray.length; i++) {
    if (Xml.selectedURL == jsonArray[i][0]) {
      var selected_i = i;
      break;
    }
  }
  for (var i = 0; i < jsonArray.length; i++) {
    if (i == selected_i) {
      continue;
    }
    if ((url == jsonArray[i][0])) {
      displayError(errorDuplicateUrl);
      return;
    } else if (name == jsonArray[i][1]) {
      displayError(errorDuplicateName);
      return;
    }
  }
  // If we get here, we're cleared to stored the edits
  jsonArray[selected_i][0] = url;
  jsonArray[selected_i][1] = name;

  window.localStorage.setItem('wikindx-localStorage', JSON.stringify(jsonArray));
  Xml.getUrlSelectBox(jsonArray);
  Visible.visibleElements.push("wikindx-search-parameters");
  Visible.retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  displaySuccess(successEditUrl);
  return;
}
export function urlAddDisplay() {
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Add WIKINDX';
  urlManagementDisplay("wikindx-url-entry");
}
export function urlRegister() {
  var newUrl = document.getElementById("wikindx-new-url");
  var url = newUrl.value.trim();
  var newName = document.getElementById("wikindx-new-url-name");
  var name = newName.value.trim();
  if (!url) {
    displayError(errorNewUrl);
    return;
  }
  if (!name) {
    displayError(errorNewUrl);
    return;
  }
  // Add trailing '/'
  if (url.slice(-1) != '/') {
    url += '/';
  }
  var hrReturn = Xml.heartbeat(url);
  if (hrReturn !== true) {
    displayError(hrReturn);
    return false;
  }
  if (window.localStorage.getItem('wikindx-localStorage') == null) {
    var jsonArray = [[url, name]];
  } else {
    var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
    var len = jsonArray.length;
    var i;
    for (i = 0; i < len; i++) {
      if ((url == jsonArray[i][0])) {
        displayError(errorDuplicateUrl);
        return;
      } else if (name == jsonArray[i][1]) {
        displayError(errorDuplicateName);
        return;
      }
    }
    jsonArray.push([url, name]);
  }
  LocalStorage.incrementNumStoredURLs();
  if (LocalStorage.numStoredURLs > 1) {
    document.getElementById("wikindx-url-preferred").style.display = "block";
  }
  window.localStorage.setItem('wikindx-localStorage', JSON.stringify(jsonArray));
  Xml.getUrlSelectBox(jsonArray);
  Visible.visibleElements.push("wikindx-search-parameters");
  Visible.visibleElements.push("wikindx-action");
  Visible.retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  Styles.styleSelectBox(); // Preload with first value from wikindx-url select box
  displaySuccess(successNewUrl + name + ' (' + url + ')');
  return;
}
export function urlPreferredDisplay() {
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var len = jsonArray.length;
  var i, id;
  var text = '';
  var checked = 'checked';
  for (i = 0; i < len; i++) {
    if (i) {
      checked = '';
    }
    id = jsonArray[i][1]; // The WIKINDX name
    text += '<input type="radio" id="' + id + '" name="wikindx-preferred" value="' + id + '"' + checked + '>'
      + '<label for="' + id + '"> ' + jsonArray[i][1] + ': ' + jsonArray[i][0] + '</label><br/>';
  }
  text += '<button class="button" id="wikindx-url-prefer" alt="Set preferred WIKINDX" title="Set preferred WIKINDX">Store</button>';
  text += '<button class="button" id="wikindx-close-url-preferred" alt="Close" title="Close">Close</button>';
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Preferred WIKINDX';
  document.getElementById("wikindx-urls-preferred").innerHTML = text;
  urlManagementDisplay("wikindx-urls-preferred");
  document.getElementById("wikindx-url-prefer").onclick = urlPrefer;
  document.getElementById("wikindx-close-url-preferred").onclick = wikindxClose;
}
function urlPrefer() {
  // What is in position [0] of the array is the preferrred URL . . .
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var name = document.querySelector('input[name="wikindx-preferred"]:checked').value;
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
    window.localStorage.setItem('wikindx-localStorage', JSON.stringify(newArray));
    Xml.getUrlSelectBox(newArray);
    Styles.styleSelectBox();
  }
  Visible.retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  document.getElementById("wikindx-search-parameters").style.display = "block";
  displaySuccess(successPreferredUrl);
  return;
}
export function urlDeleteDisplay() {
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var len = jsonArray.length;
  var i, id;
  var text = '';
  for (i = 0; i < len; i++) {
    id = jsonArray[i][0] + '--WIKINDX--' + jsonArray[i][1];
    text += '<input type="checkbox" id="' + id + '" name="' + id + '">'
      + '<label for="' + id + '"> ' + jsonArray[i][1] + ': ' + jsonArray[i][0] + '</label><br/>';
  }
  text += '<button class="button" id="wikindx-url-remove" alt="Delete URLs" title="Delete URLs">Delete URLs</button>';
  text += '<button class="button" id="wikindx-close-url-remove" alt="Close" title="Close">Close</button>';
  document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Delete WIKINDX';
  document.getElementById("wikindx-urls-remove").innerHTML = text;
  urlManagementDisplay("wikindx-urls-remove");
  document.getElementById("wikindx-url-remove").onclick = urlRemove;
  document.getElementById("wikindx-close-url-remove").onclick = wikindxClose;
}
function urlRemove() {
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  var len = jsonArray.length;
  var i, id;
  var removeCount = 0;
  var split = [];
  var keep = [];

  // First compare
  for (i = 0; i < len; i++) {
    id = jsonArray[i][0] + '--WIKINDX--' + jsonArray[i][1];
    if (document.getElementById(id).checked == false) {
      split = id.split('--WIKINDX--');
      keep.push([split[0], split[1]]);
    } else {
      ++removeCount;
      LocalStorage.decrementNumStoredURLs();
    }
  }
  if (!removeCount) {
    urlDeleteDisplay();
    return;
  }
  if (LocalStorage.numStoredURLs < 2) {
    document.getElementById("wikindx-url-preferred").style.display = "none";
  }
  if (removeCount == len) { // Have we completely emptied the list?
    window.localStorage.removeItem('wikindx-localStorage');
    document.getElementById("wikindx-about-begin").style.display = "block";
    document.getElementById("wikindx-urls-remove").style.display = "none";
    document.getElementById("wikindx-action").style.display = "none";
    urlAddDisplay();
    document.getElementById("wikindx-close-url-entry").style.display = "none";
    displaySuccess(successRemoveAllUrls);
    return;
  }
  window.localStorage.setItem('wikindx-localStorage', JSON.stringify(keep));
  Xml.getUrlSelectBox(keep);
  Styles.styleSelectBox(); // Preload with first value from wikindx-url select box
  document.getElementById("wikindx-url-management").style.display = "none";
  Visible.retrieveVisible();
  displaySuccess(successRemoveUrl);
}


export function wikindxClose() {
  Visible.retrieveVisible();
  document.getElementById("wikindx-url-management").style.display = "none";
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-search-parameters").style.display = "block";
}