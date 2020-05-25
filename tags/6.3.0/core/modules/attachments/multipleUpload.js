/* Adapted from: https://code-boxx.com/simple-drag-and-drop-file-upload/ */

/* !! UPDATE : AJAX IS ASYNCHRONOUS !! */
/* We do not want users to dump 100 files & upload all at the same time */
/* This will create sort of a queue system & upload one at a time */
var upcontrol = {
  queue : null, // upload queue
  now : 0, // current file being uploaded
  start : function (files) {
  // upcontrol.start() : start upload queue

    // WILL ONLY START IF NO EXISTING UPLOAD QUEUE
    if (upcontrol.queue==null) {
      // VISUAL - DISABLE UPLOAD UNTIL DONE
      upcontrol.queue = files;
      document.getElementById('uploader').classList.add('disabled');

      // PROCESS UPLOAD - ONE BY ONE
      upcontrol.run();
    }
  },
  run : function () {
  // upcontrol.run() : proceed upload file
    var xhr = new XMLHttpRequest(),
        data = new FormData();
    data.append('file', upcontrol.queue[upcontrol.now]);
    var url = '';
    url = url.concat('index.php?action=attachments_ATTACHMENTS_CORE&method=init&function=addDragAndDrop', 
    	'&resourceId=', rId, '&MAX_FILE_SIZE=', maxSize);
    xhr.open('POST', url, true);
    xhr.onload = function (e) {
      // SHOW UPLOAD STATUS
 //     var fstat = document.createElement('div'),
 //         txt = upcontrol.queue[upcontrol.now].name + " - ";
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          // SERVER RESPONSE
          null;
//          txt += xhr.responseText;
        } else {
          // ERROR
 //         txt += xhr.statusText;
			upcontrol.now = 0;
			upcontrol.queue = null;
			document.getElementById('uploader').classList.remove('disabled');
			window.location.href = errorUrl;
        }
      }
//      fstat.innerHTML = txt;
//    document.getElementById('upstat').appendChild(fstat);

      // UPLOAD NEXT FILE
      upcontrol.now++;
      if (upcontrol.now < upcontrol.queue.length) {
        upcontrol.run();
      }
      // ALL DONE
      else {
        upcontrol.now = 0;
        upcontrol.queue = null;
        document.getElementById('uploader').classList.remove('disabled');
        window.location.href = successUrl;
      }
    };
    xhr.send(data);
  }
};

window.addEventListener("load", function () {
  // IF DRAG-DROP UPLOAD SUPPORTED
  if (window.File && window.FileReader && window.FileList && window.Blob) {
    /* [THE ELEMENTS] */
    var uploader = document.getElementById('uploader');

    /* [VISUAL - HIGHLIGHT DROP ZONE ON HOVER] */
    uploader.addEventListener("dragenter", function (e) {
      e.preventDefault();
      e.stopPropagation();
      uploader.classList.add('highlight');
    });
    uploader.addEventListener("dragleave", function (e) {
      e.preventDefault();
      e.stopPropagation();
      uploader.classList.remove('highlight');
    });

    /* [UPLOAD MECHANICS] */
    // STOP THE DEFAULT BROWSER ACTION FROM OPENING THE FILE
    uploader.addEventListener("dragover", function (e) {
      e.preventDefault();
      e.stopPropagation();
    });

    // ADD OUR OWN UPLOAD ACTION
    uploader.addEventListener("drop", function (e) {
      e.preventDefault();
      e.stopPropagation();
      uploader.classList.remove('highlight');
      upcontrol.start(e.dataTransfer.files);
    });
  }
  // FALLBACK - HIDE DROP ZONE IF DRAG-DROP UPLOAD NOT SUPPORTED
  else {
    document.getElementById('uploader').style.display = "none";
      var fstat = document.createElement('div');
      fstat.innerHTML = fallback;
    document.getElementById('fallback').appendChild(fstat);
  }
});