/**
 * Get a page an call a function on success
 *
 * @param {string}                            url           The page to get
 * @param {function(content: string): void}   callback      A function to call (with the content received)
 */
var get = function(url, callback) {
  console.log('get : ' + url);
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      callback(this.responseText);
    }
  }
  xhttp.open('GET', url, true);
  xhttp.send();
}

class App {
  constructor() {
    this.getRequests();
  }

  addCurrent() {
    app.add(document.getElementById('textinput').value)
  }

  add(description) {
    var tagsUrl = 'index.php?controller=demandes&action=api&add=' + description;
    console.log('url : ' + tagsUrl);

    get(tagsUrl, app.getRequests.bind(app));
  }

  remove(id) {
    var tagsUrl = 'index.php?controller=demandes&action=api&remove=' + id;
    console.log('url : ' + tagsUrl);
    
    get(tagsUrl, app.getRequests.bind(app));
  }

  getRequests() {
    var tagsUrl = 'index.php?controller=demandes&action=api';
    console.log('url : ' + tagsUrl);
    get(tagsUrl, function(content) {
      var parsed = JSON.parse(content);

      var newData = [];

      var length = parsed.length;
      for(var i = 0; i < length; i++) {
        newData[i] = {};

        newData[i].id = parsed[i].id;
        newData[i].description = parsed[i].description;
      }

      app.displayRequests(newData);
    });
  }

  displayRequests(requests) {
    var html = '<ul class="collection with-header">';

    for(var i = 0; i < requests.length; i++) {
      html += '<li class="collection-item"><div>';
      html += requests[i].description;
      html += '<span onclick="app.remove(' + requests[i].id + ')" href="" class="secondary-content"><i class="material-icons">clear</i></span>';
      html += '</div></li>';
    }

    html += '</ul>';

    document.getElementById('body').innerHTML = html;
  }
}

var app = new App();

/*/
$(document).ready(function() {
  // ...
  app.initWave();
});
/*/

document.addEventListener('DOMContentLoaded', function(){
   // ..
}, false);
