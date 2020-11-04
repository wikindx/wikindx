// gatekeeper.js

function redirectSet(url)
{
	var browserTabID;
	if (sessionStorage.getItem('browserTabID') == null) {
		browserTabID = uuidv4();
		sessionStorage.setItem('browserTabID', browserTabID);
	} else { // This shouldn't be necessary but is here for completeness. Perhaps of use in the future
		browserTabID = sessionStorage.getItem('browserTabID');
	}
	url = 'index.php?' + url + '&browserTabID=' + browserTabID;
	window.location.href = url;
}
function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}