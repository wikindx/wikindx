function styleSelectBox(url) {
  var response = getStyles(url);
  if (response.xmlResponse === false) {
    return {
      xmlResponse: false,
      message: response.message
    };
  }
/////////
  // All OK . . .
  var styleLong, styleShort, i;
  var styleSelectBox = '';
  var jsonArray = response.xmlArray;
  for (var i = 0; i < jsonArray.length; i++) {
    styleShort = jsonArray[i].styleShort;
    styleLong = jsonArray[i].styleLong;
    styleSelectBox += '<option value="' + styleShort + '">' + styleLong + '</option>';
  }
  if (!styleSelectBox) {
    return {
      xmlResponse: false,
      message: errorStyles
    };
  }
  return {
    xmlResponse: true,
    styleSelectBox: styleSelectBox,
    selectedURL: url
  };
}
