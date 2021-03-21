
import { xmlResponse } from "./wikindxXml";

var errorAccess = 'The WIKINDX admin has not enabled read-only access.';
var errorCompatibility = 'ERROR: Incompatibility between add-in and WIKINDX.';

export function displayError(error) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-success").style.display = "none";
  document.getElementById("wikindx-finalize-working").style.display = "none";
  document.getElementById("wikindx-search-working").style.display = "none";

  var displayErr = document.getElementById("wikindx-error");
// The following conditionals are because there are mysterious 
// issues sending messages from heartbeat() in wikindxXml.js. This is a workaround . . .
  if (error == undefined) {
    error = "ERROR: XMLHTTP error – could not connect to the WIKINDX.  <br/><br/>There could be any number of reasons for this including an incorrect WIKINDX URL, an incompatibility between this add-in and the WIKINDX, the WIKINDX admin has not enabled read-only access, a network error . . .";
  }
  if(xmlResponse == 'incompatible') {
    error = errorCompatibility;
  } else if(xmlResponse == 'access denied') {
    error = errorAccess;
  }
  displayErr.innerHTML = error;
  displayErr.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}
export function displaySuccess(success) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-error").style.display = "none";

  var displaySucc = document.getElementById("wikindx-success");
  displaySucc.innerHTML = success;
  displaySucc.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}
