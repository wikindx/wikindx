import * as Visible from "./wikindxVisible";
import * as Xml from "./wikindxXml";
import * as UrlManagement from "./wikindxUrlManagement";
import * as Styles from "./wikindxStyles";

export var numStoredURLs = 0;

export function checkLocalStorage(displayUrlEntry) {
  if (!localStorage()) {
    document.getElementById("wikindx-search-parameters").style.display = "none";
    document.getElementById("wikindx-close-url-entry").style.display = "none";
    if (displayUrlEntry) {
      document.getElementById("wikindx-url-management-subtitle").innerHTML = 'Add WIKINDX';
      UrlManagement.urlManagementDisplay("wikindx-url-entry");
      document.getElementById("wikindx-about").style.display = "none";
    } else {
      document.getElementById("wikindx-url-management").style.display = "none";
      document.getElementById("wikindx-about").style.display = "block";
      document.getElementById("wikindx-about-begin").style.display = "block";
      document.getElementById("wikindx-display-about").src = "../../assets/lightbulb.png";
    }
    return false;
  } else {
    if (!Visible.visibleElements.length) {
      document.getElementById("wikindx-about-begin").style.display = "none";
      document.getElementById("wikindx-url-management").style.display = "none";
      document.getElementById("wikindx-search-parameters").style.display = "block";
      Styles.styleSelectBox(); // Preload with first value from wikindx-url select box
    } else {
      Visible.retrieveVisible();
    }
  }
  return true;
}
export function localStorage() {
  if (window.localStorage.getItem('wikindx-localStorage') == null) {
    return false;
  }
  var jsonArray = JSON.parse(window.localStorage.getItem('wikindx-localStorage'));
  numStoredURLs = jsonArray.length;
  if (numStoredURLs > 1) {
    document.getElementById("wikindx-url-preferred").style.display = "block";
  }
  Xml.getUrlSelectBox(jsonArray);
  return true;
}

export function incrementNumStoredURLs() {
  ++numStoredURLs;
}

export function decrementNumStoredURLs() {
  -numStoredURLs;
}