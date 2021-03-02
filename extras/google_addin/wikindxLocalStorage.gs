function getLocalStorage() {
  var userProperties = PropertiesService.getUserProperties();
// Debugging only – comment out in production!
//  userProperties.deleteAllProperties();
  return {
    localStorage: userProperties.getProperty('wikindx-localStorage')
  };
}

function setLocalStorage(jsonArray) {
  var userProperties = PropertiesService.getUserProperties();
  userProperties.setProperty('wikindx-localStorage', JSON.stringify(jsonArray));
}
