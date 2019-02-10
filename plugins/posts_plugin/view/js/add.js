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

var sliceSize = 1000 * 1024;
var fileReader = null;
var file = null;

function upload(elmt) {
/*/
var url = g_baseUrl + 'posts/upload';
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
/*/

  fileReader = new FileReader();
  file = elmt.files[0];

  if(file.size < 10 * sliceSize) {
    var reader = new FileReader();
    reader.onload = function(event) {
      document.getElementById('sampleImg').src = reader.result;
    }
    reader.readAsDataURL(file);
  }

  document.getElementById('file_button').classList.remove('is-success');
  document.getElementById('file_button').classList.add('is-warning');
  document.getElementById('file_button').disabled = true;
  document.getElementById('file_button').innerHTML = 'Upload...';

  uploadFile(0, false);
}

function uploadFile(start, fileId) {
  if(file == null || fileReader == null) {
    return;
  }
  console.log(file.type);

  var nextSlice = start + sliceSize + 1;
  var filePart = file.slice(start, nextSlice);

  fileReader.onload = function(event) { // use onloadend ?
    post(g_baseUrl + '/posts/upload?u=1', function(data) {
      console.log(data);
      var parsed = JSON.parse(data);

      if(parsed.error == 1) {
        document.getElementById('file_button').classList.remove('is-warning');
        document.getElementById('file_button').classList.remove('is-success');
        document.getElementById('file_button').classList.add('is-error');
        document.getElementById('file_button').innerHTML = 'Error';
        document.getElementById('submitUpload').disabled = true;

        return;
      }

      var sizeDone = start + sliceSize;
      var percentDone = Math.floor(sizeDone / file.size * 100);

      if(nextSlice < file.size) {
        document.getElementById('file_button').innerHTML = percentDone + '%...';

        uploadFile(nextSlice, parsed['id']);
      } else {
        uploadedFile = parsed['id'];
        document.getElementById('uploadedFile').value = parsed['id'];

        document.getElementById('file_button').classList.remove('is-warning');
        document.getElementById('file_button').classList.add('is-success');
        document.getElementById('file_button').innerHTML = 'OK!';
        document.getElementById('submitUpload').disabled = false;
      }

    }, {
      action: 'upload',
      file_data: event.target.result,
      file: file.name,
      file_type: file.type,
      id: fileId
    });
  }

  fileReader.readAsDataURL(filePart);
}
