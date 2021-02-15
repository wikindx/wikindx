
var ooxmlInsertInit = "<w:p xmlns:w='http://schemas.microsoft.com/office/word/2003/wordml'>"; // Tried others - only 2003 works for vanish . . .
var ooxmlVanishStart = "<w:r><w:rPr><w:vanish/></w:rPr><w:t>";
var ooxmlVanishEnd = "</w:t></w:r>";
var ooxmlTextStart = "<w:r><w:t>";
var ooxmlTextEnd = "</w:t></w:r>";
var ooxmlInsertEnd = "</w:p>";
// ooxmlTagStart <value of tag> ooxmlTagMiddle ooxmlTextStart <visible text> ooxmlTextEnd ooxmlTagEnd
//var ooxmlTagStart = '<w:p xmlns:w="http://schemas.microsoft.com/office/word/2003/wordml"><w:sdt><w:sdtPr><w:tag w:val="';
var ooxmlTagStart = '<w:p xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:sdt><w:sdtPr><w:tag w:val="';
var ooxmlTagMiddle = '"/></w:sdtPr><w:sdtContent>';
var ooxmlTagEnd = '</w:sdtContent></w:sdt></w:p>';


Word.run((context) => {
  let options = Word.SearchOptions.newObject(context);
  options.matchWildCards = false;
  options.ignorePunct = true;
  options.ignoreSpace = true;
  options.matchPrefix = true;
  options.matchSuffix = true;
  options.matchWildCards = true;
  let rangesFind = context.document.body.search('[_MyText]', options);
  context.load(rangesFind);
  return context.sync().then(() => {
      console.log(rangesFind.items);
      rangesFind.items.forEach((item) => {
          console.log(item.text);
          item.insertText('REPLACED', Word.InsertLocation.replace);
      });
  });
});
  
  function addXMLPartandHandler() {
    Office.context.document.customXmlParts.addAsync(
        "<testns:book xmlns:testns='http://testns.com'><testns:page number='1'>Hello</testns:page><testns:page number='2'>world!</testns:page></testns:book>",
        function(r) {
            console.log('1 addAsyncID: ' + r.value.id); 
            Office.context.document.settings.set('WikindxID', r.value.id);
            r.value.addHandlerAsync(Office.EventType.DataNodeDeleted,
            function(a) {
              console.log(a.type); 
              console.log(a);
            },
                function(s) {
                  console.log('2 ' + s.status);
                });
        });
        Office.context.document.settings.saveAsync();
        var wikindxXmlID = Office.context.document.settings.get('WikindxID');
        console.log('3 WikindxID: ' + wikindxXmlID);
        Office.context.document.bindings.getByIdAsync(wikindxXmlID, function (asyncResult) {
          console.log('4 ' + asyncResult.status);
        });
  }
  
  function createCustomXmlPartAndStoreID() {
    const xmlString = "<Reviewers xmlns='http://schemas.contoso.com/review/1.0'><Reviewer>Mark</Reviewer><Reviewer>Hong</Reviewer><Reviewer>Sally</Reviewer></Reviewers>";
    Office.context.document.customXmlParts.addAsync(xmlString,
        (asyncResult) => {
            Office.context.document.settings.set('ReviewersID', asyncResult.id);
            console.log('SET: ' + asyncResult.id);
            Office.context.document.settings.saveAsync(function (asyncResult) {
              if (asyncResult.status == Office.AsyncResultStatus.Failed) {
                  console.log('Settings save failed. Error: ' + asyncResult.error.message);
              } else {
                  console.log('Settings saved.');
              }
            });
        }
    );
    getReviewers();
  }
  function getReviewers() {
    const reviewersXmlId = Office.context.document.settings.get('ReviewersID');
    console.log('GOT: ' + reviewersXmlId);
    Office.context.document.customXmlParts.getByIdAsync(reviewersXmlId,
        (asyncResult) => {
            asyncResult.value.getXmlAsync(
                (asyncResult) => {
                    $("#xml-blob").text(asyncResult.value);
                }
            );
        }
    );
  
  }


function finalize()
{ var doc = new openXml.OpenXmlPackage(openXmlDocument);
  Word.run(function (context) {
    
    // Create a proxy object for the document.
    var body = context.document.body;
    
    // Queue a command to load content control properties.
    context.load(body, 'contentControls/id, contentControls/text, contentControls/tag');
    
    // Synchronize the document state by executing the queued commands, 
    // and return a promise to indicate task completion.
    return context.sync().then(function () {
        if (thisDocument.contentControls.items.length !== 0) {
            for (var i = 0; i < thisDocument.contentControls.items.length; i++) {
                console.log(thisDocument.contentControls.items[i].id);
                console.log(thisDocument.contentControls.items[i].text);
                console.log(thisDocument.contentControls.items[i].tag);
            }
        } else {
            console.log('No content controls in this document.');
        }
    });  
  })
  .catch(function (error) {
      console.log('Error: ' + JSON.stringify(error));
      if (error instanceof OfficeExtension.Error) {
          console.log('Debug info: ' + JSON.stringify(error.debugInfo));
      }
  });
}

async function insertContentControls() {
  // Traverses each paragraph of the document and wraps a content control on each with either a even or odd tags.
  await Word.run(async (context) => {
    let paragraphs = context.document.body.paragraphs;
    paragraphs.load("$none"); // Don't need any properties; just wrap each paragraph with a content control.

    await context.sync();

    for (let i = 0; i < paragraphs.items.length; i++) {
      let contentControl = paragraphs.items[i].insertContentControl();
      // For even, tag "even".
      if (i % 2 === 0) {
        contentControl.tag = "even";
      } else {
        contentControl.tag = "odd";
      }
    }
    console.log("Content controls inserted: " + paragraphs.items.length);

    await context.sync();
  });
}

async function modifyContentControls() {
  // Adds title and colors to odd and even content controls and changes their appearance.
  await Word.run(async (context) => {
    // Gets the complete sentence (as range) associated with the insertion point.
    let evenContentControls = context.document.contentControls.getByTag("even");
    let oddContentControls = context.document.contentControls.getByTag("odd");
    evenContentControls.load("length");
    oddContentControls.load("length");

    await context.sync();

    for (let i = 0; i < evenContentControls.items.length; i++) {
      // Change a few properties and append a paragraph
      evenContentControls.items[i].set({
        color: "red",
        title: "Odd ContentControl #" + (i + 1),
        appearance: "Tags"
      });
      evenContentControls.items[i].insertParagraph("This is an odd content control", "End");
    }

    for (let j = 0; j < oddContentControls.items.length; j++) {
      // Change a few properties and append a paragraph
      oddContentControls.items[j].set({
        color: "green",
        title: "Even ContentControl #" + (j + 1),
        appearance: "Tags"
      });
      oddContentControls.items[j].insertHtml("This is an <b>even</b> content control", "End");
    }

    await context.sync();
  });
}

async function setup() {
  await Word.run(async (context) => {
    context.document.body.clear();
    context.document.body.insertParagraph("One more paragraph. ", "Start");
    context.document.body.insertParagraph("Inserting another paragraph. ", "Start");
    context.document.body.insertParagraph(
      "Video provides a powerful way to help you prove your point. When you click Online Video, you can paste in the embed code for the video you want to add. You can also type a keyword to search online for the video that best fits your document.",
      "Start"
    );
    context.document.body.paragraphs
      .getLast()
      .insertText(
        "To make your document look professionally produced, Word provides header, footer, cover page, and text box designs that complement each other. For example, you can add a matching cover page, header, and sidebar. Click Insert and then choose the elements you want from the different galleries. ",
        "Replace"
      );
  });
}

/** Default helper for invoking an action and handling errors. */
async function tryCatch(callback) {
  try {
    await callback();
  } catch (error) {
    // Note: In a production add-in, you'd want to notify the user through your add-in's UI.
    console.error(error);
  }
}