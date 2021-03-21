/**
 * @OnlyCurrentDoc
 *
 * The above comment directs Apps Script to limit the scope of file
 * access for this add-on. It specifies that this add-on will only
 * attempt to read or modify the files in which the add-on is used,
 * and not all of the user's files. The authorization request message
 * presented to users will reflect this limited scope.
 */

/**
 * Compatibility number. Must be equal to office.php's $officeVersion
 */
var compatibility = 1;

/** Error messages */
var errorJSON = "ERROR: Unspecified error. This could be any number of things from not being able to connect to the WIKINDX to no resources found matching your search.";
var errorAccess = 'The WIKINDX admin has not enabled read-only access.';
var errorCompatibility = 'ERROR: Incompatibility between add-in and WIKINDX.';
var errorXMLHTTP = "ERROR: XMLHTTP error – could not connect to the WIKINDX.  <br/><br/>There could be any number of reasons for this including an incorrect WIKINDX URL, an incompatibility between this add-in and the WIKINDX, the WIKINDX admin has not enabled read-only access, a network error . . .";
var errorDuplicateUrl = "ERROR: Duplicate URL input.";
var errorDuplicateName = "ERROR: Duplicate name input.";
var errorStyles = "ERROR: Either no styles are defined on the selected WIKINDX or there are no available in-text citation styles.";
var errorStylesFinalize = "ERROR: No available in-text citation styles found in any of the wikindices used in the document.";
var errorNoResultsReferences = "No references found matching your search.";
var errorNoResultsCitations = "No citations found matching your search.";
var errorMissingID = "ERROR: Resource or citation ID not found in the selected WIKINDX.";

/** Success messages */
var successHeartbeat = "Yes, I am alive and kicking. Try searching me . . .";
var successNewUrl = "Stored new WIKINDX: ";
var successRemoveUrl = "Deleted WIKINDX URL(s).";
var successRemoveAllUrls = "Deleted all WIKINDX URLs.";
var successPreferredUrl = "Preference stored.";
var successEditUrl = "Edited WIKINDX URL.";
var errorNewUrl = "ERROR: Missing URL or name input.";
var successHeartbeat = "Yes, I am alive and kicking. Try searching me . . .";
var successRemoveUrl = "Deleted WIKINDX URL(s).";
var successRemoveAllUrls = "Deleted all WIKINDX URLs.";
var successPreferredUrl = "Preference stored.";

/**
 * Creates a menu entry in the Google Docs UI when the document is opened.
 * This method is only used by the regular add-on, and is never called by
 * the mobile add-on version.
 *
 * @param {object} e The event parameter for a simple onOpen trigger. To
 *     determine which authorization mode (ScriptApp.AuthMode) the trigger is
 *     running in, inspect e.authMode.
 */
function onOpen(e) {
  DocumentApp.getUi().createAddonMenu()
      .addItem('Insert WIKINDX references', 'showSidebar')
      .addToUi();
}

/**
 * Runs when the add-on is installed.
 * This method is only used by the regular add-on, and is never called by
 * the mobile add-on version.
 *
 * @param {object} e The event parameter for a simple onInstall trigger. To
 *     determine which authorization mode (ScriptApp.AuthMode) the trigger is
 *     running in, inspect e.authMode. (In practice, onInstall triggers always
 *     run in AuthMode.FULL, but onOpen triggers may be AuthMode.LIMITED or
 *     AuthMode.NONE.)
 */
function onInstall(e) {
  onOpen(e);
}

/**
 * Opens a sidebar in the document containing the add-on's user interface.
 * This method is only used by the regular add-on, and is never called by
 * the mobile add-on version.
 */
function showSidebar() {
  var ui = HtmlService.createHtmlOutputFromFile('sidebar')
      .setTitle('WIKINDX');
  DocumentApp.getUi().showSidebar(ui);
}

/**
 * Initialize the WIKINDX
 */
function initializeWikindx() {
  var prefs = getLocalStorage();
// Debugging only – comment out in production!
//  userProperties.deleteAllProperties();
  if(prefs.localStorage === null) { // No wikindices stored
    return {
      initialize: true,
      xmlResponse: true // faking it . . .
   };
  } // else initialize with first stored WIKINDX
  var jsonArray = JSON.parse(prefs.localStorage);
  var selectedURL = jsonArray[0][0];
  var response = styleSelectBox(jsonArray[0][0]);
  if (!response.xmlResponse) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
  var urlSelectBox = getUrlSelectBox(jsonArray);
  return {
    initialize: false,
    xmlResponse: true,
    styleSelectBox: response.styleSelectBox,
    numStoredURLs: jsonArray.length,
    selectedURL: selectedURL,
    urlSelectBox: urlSelectBox
  };
}
