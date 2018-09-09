var uploadedFile = null;

function prevent(event) {
  if(null === uploadedFile) {
    event.preventDefault();
  }
}

document.getElementById('postForm').addEventListener('submit', prevent, true);

function checkPost(e) {
  console.log('>>' + e + '<<');
  if(null === uploadedFile) {
    e.preventDefault();
  }
}

function upload(elmt) {
  var url = g_baseUrl + 'posts/upload';
  var file = elmt.files[0];

  var xhttp = new XMLHttpRequest();


  xhttp.open("POST", url, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

  xhttp.onreadystatechange = function() {
    if(xhttp.readyState == 4 && xhttp.status == 200) {
      var parsed = JSON.parse(xhttp.responseText);

      if(parsed.error == 0) {
        uploadedFile = parsed.file;
        document.getElementById('uploadedFile').value = uploadedFile;

        document.getElementById('file_button').classList.remove('is-warning');
        document.getElementById('file_button').classList.add('is-success');
        document.getElementById('file_button').innerHTML = 'OK!';
        document.getElementById('submitUpload').disabled = false;
      }
    }
  }

  xhttp.addEventListener('progress', function(e) {
    if(e.lengthComputable) {
      var percentComplete = e.loaded / e.total * 100;
      console.log(percentComplete + '%');
    } else {
      console.log('unknown length');
    }
  }, false);

  // Read the file and send to server
  var fileObj = new FileReader();
  fileObj.onload = function() {
    document.getElementById('file_button').classList.remove('is-success');
    document.getElementById('file_button').classList.add('is-warning');
    document.getElementById('file_button').disabled = true;
    document.getElementById('file_button').innerHTML = 'Upload...';

    var fullPath = elmt.value;
    var fileName = fullPath.split(/(\\|\/)/g).pop();

    document.getElementById('sampleImg').src = fileObj.result;
    // document.getElementById('test').value = fileName;
    xhttp.send('file=' + fileObj.result);
  }

  fileObj.readAsDataURL(file);
}
