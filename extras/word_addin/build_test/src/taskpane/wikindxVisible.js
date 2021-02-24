export var visibleElements = [];

export function hideVisible(idArray) {
  // store what is currently visible then hide
  visibleElements = []; // Empty out storage . . .
  for (var i = 0; i < idArray.length; i++) {
    if (document.getElementById(idArray[i]).style.display != "none") {
      visibleElements.push(idArray[i]);
      document.getElementById(idArray[i]).style.display = "none";
    }
  }
}
export function retrieveVisible() {
  // Retrieve what was visible and show again
  for (var i = 0; i < visibleElements.length; i++) {
    document.getElementById(visibleElements[i]).style.display = "block";
  }
}

export function reset() {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-search-working").style.display = "none"; 
  document.getElementById("wikindx-search-completed").style.display = "none"; 
}

export function endPrintCitation() {
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-display-ref").style.display = "none";
  document.getElementById("wikindx-insert-refSection").style.display = "none";
  document.getElementById("wikindx-refSelectBox").style.display = "none";
  document.getElementById("wikindx-display-cite").style.display = "block";
  document.getElementById("wikindx-insert-citeSection").style.display = "block";
  document.getElementById("wikindx-display-results").style.display = "block";
  document.getElementById("wikindx-citeSelectBox").style.display = "block";
}

export function endPrintReference() {
  document.getElementById("wikindx-messages").style.display = "none";
  document.getElementById("wikindx-display-cite").style.display = "none";
  document.getElementById("wikindx-insert-citeSection").style.display = "none";
  document.getElementById("wikindx-citeSelectBox").style.display = "none";
  document.getElementById("wikindx-display-ref").style.display = "block";
  document.getElementById("wikindx-insert-refSection").style.display = "block";
  document.getElementById("wikindx-display-results").style.display = "block";
  document.getElementById("wikindx-refSelectBox").style.display = "block";
}

export function displayReferencePane() {
  hideVisible(["wikindx-messages", "wikindx-display-results", "wikindx-citation-order"]);
  document.getElementById("wikindx-search-completed").style.display = "none";
  document.getElementById("wikindx-url-management").style.display = "none";
  document.getElementById("wikindx-action-title-citations").style.display = "none";
  document.getElementById("wikindx-action-title-finalize").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-search-completed").style.display = "none";
  document.getElementById("wikindx-search-working").style.display = "none";
  document.getElementById("wikindx-citations-help").style.display = "none";
  document.getElementById("wikindx-finalize-help").style.display = "none";
  document.getElementById("wikindx-display-citations-help").src = "../../assets/lightbulb_off.png";
  document.getElementById("wikindx-display-finalize-help").src = "../../assets/lightbulb_off.png";
  document.getElementById("wikindx-action-title-references").style.display = "block";
  document.getElementById("wikindx-reference-order").style.display = "block";
  document.getElementById("wikindx-search-parameters").style.display = "block";
}

export function displayCitationPane() {
  hideVisible(["wikindx-messages", "wikindx-display-results", "wikindx-reference-order"]);
  document.getElementById("wikindx-search-completed").style.display = "none";
  document.getElementById("wikindx-url-management").style.display = "none";
  document.getElementById("wikindx-action-title-references").style.display = "none";
  document.getElementById("wikindx-action-title-finalize").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-search-completed").style.display = "none";
  document.getElementById("wikindx-search-working").style.display = "none"; 
  document.getElementById("wikindx-references-help").style.display = "none";
  document.getElementById("wikindx-finalize-help").style.display = "none";
  document.getElementById("wikindx-display-references-help").src = "../../assets/lightbulb_off.png";
  document.getElementById("wikindx-display-finalize-help").src = "../../assets/lightbulb_off.png";
  document.getElementById("wikindx-action-title-citations").style.display = "block";
  document.getElementById("wikindx-citation-order").style.display = "block";
  document.getElementById("wikindx-search-parameters").style.display = "block";
}

export function displayFinalizePane() {
  hideVisible(["wikindx-messages", "wikindx-display-results", "wikindx-url-management", "wikindx-search-parameters"]);
  document.getElementById("wikindx-finalize-completed").style.display = "none";
  document.getElementById("wikindx-action-title-references").style.display = "none";
  document.getElementById("wikindx-action-title-citations").style.display = "none";
  document.getElementById("wikindx-citations-help").style.display = "none";
  document.getElementById("wikindx-references-help").style.display = "none";
  document.getElementById("wikindx-display-citations-help").src = "../../assets/lightbulb_off.png";
  document.getElementById("wikindx-display-references-help").src = "../../assets/lightbulb_off.png";
  document.getElementById("wikindx-action-title-finalize").style.display = "block";
}