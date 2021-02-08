

  
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