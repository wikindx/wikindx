/**
 * Display help and about messages
 */

import * as Visible from "./wikindxVisible";
import * as LocalStorage from "./wikindxLocalStorage";

export function wikindxDisplayAbout() {
// Show About
  if (document.getElementById("wikindx-about").style.display == "none") {
    if (LocalStorage.localStorage()) {
      document.getElementById("wikindx-about-begin").style.display = "none";
    } else {
      document.getElementById("wikindx-about-begin").style.display = "block";
    }
    document.getElementById("wikindx-about").style.display = "block";
    Visible.hideVisible(["wikindx-url-management", "wikindx-messages", "wikindx-display-results", "wikindx-search-parameters", 
      "wikindx-finalize", "wikindx-action-title-references", "wikindx-action-title-citations", "wikindx-action-title-finalize"]);
    document.getElementById("wikindx-display-about").src = "../../assets/lightbulb.png";
  } else {
// Hide About and turn back on what was previously visible
    document.getElementById("wikindx-about").style.display = "none";
    document.getElementById("wikindx-display-about").src = "../../assets/lightbulb_off.png";
    Visible.retrieveVisible();
    LocalStorage.checkLocalStorage(true);
  }
}

export function wikindxDisplayReferencesHelp() {
  // Show Help
  if (document.getElementById("wikindx-references-help").style.display == "none") {
    document.getElementById("wikindx-citations-help").style.display = "none";
    document.getElementById("wikindx-finalize-help").style.display = "none";
    document.getElementById("wikindx-display-citations-help").src = "../../assets/lightbulb_off.png";
    document.getElementById("wikindx-display-finalize-help").src = "../../assets/lightbulb_off.png";
    document.getElementById("wikindx-references-help").style.display = "block";
    document.getElementById("wikindx-display-references-help").src = "../../assets/lightbulb.png";
  } else {
    // Hide Help
    document.getElementById("wikindx-references-help").style.display = "none";
    document.getElementById("wikindx-display-references-help").src = "../../assets/lightbulb_off.png";
  }
}
export function wikindxDisplayCitationsHelp() {
  // Show Help
  if (document.getElementById("wikindx-citations-help").style.display == "none") {
    document.getElementById("wikindx-references-help").style.display = "none";
    document.getElementById("wikindx-finalize-help").style.display = "none";
    document.getElementById("wikindx-display-references-help").src = "../../assets/lightbulb_off.png";
    document.getElementById("wikindx-display-finalize-help").src = "../../assets/lightbulb_off.png";
    document.getElementById("wikindx-citations-help").style.display = "block";
    document.getElementById("wikindx-display-citations-help").src = "../../assets/lightbulb.png";
  } else {
    // Hide Help
    document.getElementById("wikindx-citations-help").style.display = "none";
    document.getElementById("wikindx-display-citations-help").src = "../../assets/lightbulb_off.png";
  }
}
export function wikindxDisplayFinalizeHelp() {
  // Show Help
  if (document.getElementById("wikindx-finalize-help").style.display == "none") {
    document.getElementById("wikindx-citations-help").style.display = "none";
    document.getElementById("wikindx-references-help").style.display = "none";
    document.getElementById("wikindx-display-citations-help").src = "../../assets/lightbulb_off.png";
    document.getElementById("wikindx-display-references-help").src = "../../assets/lightbulb_off.png";
    document.getElementById("wikindx-finalize-help").style.display = "block";
    document.getElementById("wikindx-display-finalize-help").src = "../../assets/lightbulb.png";
  } else {
    // Hide Help
    document.getElementById("wikindx-finalize-help").style.display = "none";
    document.getElementById("wikindx-display-finalize-help").src = "../../assets/lightbulb_off.png";
  }
}

