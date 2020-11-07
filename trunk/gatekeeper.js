// gatekeeper.js

/**
 * No browserTabID yet set so generate one and redirect
 */
function redirectSet(url, qs)
{
	var browserTabID;
	if ((sessionStorage.getItem('browserTabID') == null) || !sessionStorage.getItem('browserTabID')) {
		browserTabID = uuidv4();
		sessionStorage.setItem('browserTabID', browserTabID);
	} else {
		browserTabID = sessionStorage.getItem('browserTabID');
	}
	if (qs) {
		url = url + '&browserTabID=' + browserTabID;
	} else { // plain index.php
		url = url + '?browserTabID=' + browserTabID;
	}
	window.location.href = url;
}

/**
 * browserTabID in the URL. 
 * If the same as the session, URL is opened in existing tab/window so return doing nothing.
 * If not the same as the session (session is null probably), URL is opened in a new tab/window so generate browserTabID and redirect
 */
function getBrowserTabID(url, qs, browserTabID)
{
	if (browserTabID == sessionStorage.getItem('browserTabID')) { // Continuing in same tab/window
		return;
	}
// User has opened a link in a new tab/window – generate a new ID
	browserTabID = uuidv4();
	sessionStorage.setItem('browserTabID', browserTabID);
	if (qs) {
		url = url + '&browserTabID=' + browserTabID;
	} else { // plain index.php
		url = url + '?browserTabID=' + browserTabID;
	}
	window.location.href = url;
}

/**
 * Code from https://stackoverflow.com/questions/105034/how-to-create-a-guid-uuid
*/
function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}