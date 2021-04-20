/**
 * @OnlyCurrentDoc
 *
 * The above comment directs Apps Script to limit the scope of file
 * access for this add-on. It specifies that this add-on will only
 * attempt to read or modify the files in which the add-on is used,
 * and not all of the user's files. The authorization request message
 * presented to users will reflect this limited scope.
 */

(function(){ // GLOBAL variables accessible in other scripts – must be defined!
/**
 * Compatibility number. Must be equal to office.php's $officeVersion
 */
compatibility = 1;

/** Error messages */
errorJSON = "ERROR: Unspecified error. This could be any number of things from not being able to connect to the WIKINDX to no resources found matching your search.";
errorAccess = 'The WIKINDX admin has not enabled read-only access.';
errorCompatibility = 'ERROR: Incompatibility between add-in and WIKINDX.';
errorXMLHTTP = "ERROR: XMLHTTP error – could not connect to the WIKINDX.  <br/><br/>There could be any number of reasons for this including an incorrect WIKINDX URL, an incompatibility between this add-in and the WIKINDX, the WIKINDX admin has not enabled read-only access, a network error . . .";
errorDuplicateUrl = "ERROR: Duplicate URL input.";
errorDuplicateName = "ERROR: Duplicate name input.";
errorStyles = "ERROR: Either no styles are defined on the selected WIKINDX or there are no available in-text citation styles.";
errorStylesFinalize = "ERROR: No available in-text citation styles found in any of the wikindices used in the document.";
errorNoResultsReferences = "No references found matching your search.";
errorNoResultsCitations = "No citations found matching your search.";
errorMissingID = "ERROR: Resource or citation ID not found in the selected WIKINDX.";
errorNewUrl = "ERROR: Missing URL or name input.";

/** Success messages */
successHeartbeat = "Yes, I am alive and kicking. Try searching me . . .";
successNewUrl = "Stored new WIKINDX: ";
successRemoveUrl = "Deleted WIKINDX URL(s).";
successRemoveAllUrls = "Deleted all WIKINDX URLs.";
successPreferredUrl = "Preference stored.";
successEditUrl = "Edited WIKINDX URL.";
successHeartbeat = "Yes, I am alive and kicking. Try searching me . . .";
successRemoveUrl = "Deleted WIKINDX URL(s).";
successRemoveAllUrls = "Deleted all WIKINDX URLs.";
successPreferredUrl = "Preference stored.";

/** Used in wikindxInsert */
deleteArray = [];
transformArray = [];
  
/** Used in wikindxFinalize */
finalizeOrder = 'year';
finalizeAscDesc = 'ASC';
finalizeFoundBibliography = false;
finalizeMultipleWikindices = false;
finalizeXmlError = false;
finalizebibliography = '';
finalizeReferences = [];
finalizeMultipleOrder = [];
finalizeWikindices = [];
finalizeCleanIDs = new Object();
finalizeCiteIDs = new Object();
})();

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
 * Creates a menu entry in the Google Docs UI when the document is opened.
 * This method is only used by the regular add-on, and is never called by
 * the mobile add-on version.
 *
 * @param {object} e The event parameter for a simple onOpen trigger. To
 *     determine which authorization mode (ScriptApp.AuthMode) the trigger is
 *     running in, inspect e.authMode.
 */
function onOpen(e) {
  var menu = DocumentApp.getUi().createAddonMenu(); // Or DocumentApp.
  if (e && e.authMode == ScriptApp.AuthMode.NONE) {
    // Add a normal menu item (works in all authorization modes).
    menu.addItem('Insert WIKINDX references', 'showSidebar');
  } else {
    // Add a menu item based on properties (doesn't work in AuthMode.NONE).
    var properties = PropertiesService.getDocumentProperties();
    var workflowStarted = properties.getProperty('workflowStarted');
    if (workflowStarted) {
      menu.addItem('Check workflow status', 'checkWorkflow');
    } else {
      menu.addItem('Insert WIKINDX references', 'showSidebar');
    }
  }
  menu.addToUi();
/**
  DocumentApp.getUi().createAddonMenu()
      .addItem('Insert WIKINDX references', 'showSidebar')
      .addToUi();
      */
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

